<?php

declare(strict_types=1);

namespace App\Database;

use App\Exception\UserException\InvalidParameterException;
use App\Service\Security\CredentialFingerprint;
use App\Service\Security\TwoFactorState;
use App\Service\Security\TwoFactorStore;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTwoFactorStore implements TwoFactorStore
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->connection = $manager->getConnection();
    }

    public function find(int $userId) : ?TwoFactorState
    {
        $row = $this->connection->fetchAssociative('SELECT userId, revision, encryptedSecret, lastCounter FROM user_two_factor WHERE userId = ?', [$userId]);

        return $row === false ? null : new TwoFactorState((int) $row['userId'], $row['revision'], $row['encryptedSecret'], $row['lastCounter'] === null ? null : (int) $row['lastCounter']);
    }

    public function configure(int $userId, ?string $expectedRevision, ?string $encryptedSecret, ?int $counter, array $recoveryHashes, string $expectedFingerprint) : TwoFactorState
    {
        return $this->connection->transactional(function () use ($userId, $expectedRevision, $encryptedSecret, $counter, $recoveryHashes, $expectedFingerprint) : TwoFactorState {
            // Lock the existing user even when no factor row exists yet.
            $user = $this->connection->fetchAssociative('SELECT password, encryptionKey, privilegeLevel FROM users WHERE id = ? FOR UPDATE', [$userId]);
            $this->connection->fetchOne('SELECT userId FROM user_two_factor WHERE userId = ? FOR UPDATE', [$userId]);
            $current = $this->find($userId);

            if ($user === false || $current?->revision !== $expectedRevision) {
                throw InvalidParameterException::invalidFieldValue('security settings changed; reload the page');
            }
            $credentials = CredentialFingerprint::credentials($user['password'], $user['encryptionKey'], (int) $user['privilegeLevel']);

            if ( ! hash_equals(CredentialFingerprint::withRevision($credentials, $current?->revision), $expectedFingerprint)) {
                throw InvalidParameterException::invalidFieldValue('account credentials changed; log in again');
            }

            if ($encryptedSecret !== null && $current?->encryptedSecret === $encryptedSecret && $current->lastCounter !== null) {
                $counter = max($counter ?? 0, $current->lastCounter);
            }
            $revision = bin2hex(random_bytes(32));
            $values = ['encryptedSecret' => $encryptedSecret, 'lastCounter' => $counter, 'revision' => $revision];

            if ($current === null) {
                $this->connection->insert('user_two_factor', ['userId' => $userId, ...$values]);
            } else {
                $this->connection->update('user_two_factor', $values, ['userId' => $userId]);
            }
            $this->connection->delete('user_two_factor_recovery', ['userId' => $userId]);

            foreach ($recoveryHashes as $hash) {
                $this->connection->insert('user_two_factor_recovery', ['userId' => $userId, 'codeHash' => $hash, 'revision' => $revision]);
            }

            return new TwoFactorState($userId, $revision, $encryptedSecret, $counter);
        });
    }

    public function consumeCounter(int $userId, string $revision, int $counter) : bool
    {
        return $this->connection->executeStatement('UPDATE user_two_factor SET lastCounter = ? WHERE userId = ? AND revision = ? AND encryptedSecret IS NOT NULL AND (lastCounter IS NULL OR lastCounter < ?)', [$counter, $userId, $revision, $counter]) === 1;
    }

    public function consumeRecovery(int $userId, string $revision, string $hash) : bool
    {
        return $this->connection->transactional(function () use ($userId, $revision, $hash) : bool {
            $this->connection->fetchOne('SELECT userId FROM user_two_factor WHERE userId = ? FOR UPDATE', [$userId]);
            $state = $this->find($userId);

            if ($state === null || ! $state->enabled() || $state->revision !== $revision) {
                return false;
            }

            return $this->connection->delete('user_two_factor_recovery', ['userId' => $userId, 'revision' => $revision, 'codeHash' => $hash]) === 1;
        });
    }
}
