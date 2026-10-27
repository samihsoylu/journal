<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidParameterException;
use App\Utility\Encryptor;
use App\Utility\Session;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Psr\Clock\ClockInterface;

final readonly class TwoFactorService
{
    private const string SETUP = 'two_factor_setup';

    public function __construct(
        private TwoFactorStore $store,
        private UserRepository $users,
        private Totp $totp,
        private RecoveryCodes $recovery,
        private Encryptor $encryptor,
        private AttemptLimiter $attempts,
        private ClockInterface $clock,
    ) {}

    public function beginSetup(int $userId, Key $key, string $password) : void
    {
        $user = $this->verifyPassword($userId, $password);
        $state = $this->store->find($userId);

        if ($state?->enabled()) {
            throw InvalidParameterException::invalidFieldValue('two-factor authentication is already enabled');
        }
        Session::put(self::SETUP, [
            'userId' => $userId, 'fingerprint' => CredentialFingerprint::from($user, $state?->revision),
            'credentials' => CredentialFingerprint::credentials($user->getPassword(), $user->getEncryptionKey(), $user->getPrivilegeLevel()),
            'revision' => $state?->revision, 'secret' => $this->encryptor->encrypt($this->totp->generateSecret(), $key),
            'expires' => $this->clock->now()->getTimestamp() + 600,
        ]);
    }

    public function setup(int $userId, Key $key) : ?array
    {
        $setup = Session::get(self::SETUP);
        $user = $this->users->getById($userId);
        $state = $this->store->find($userId);

        if ( ! is_array($setup) || ($setup['userId'] ?? null) !== $userId || ! is_int($setup['expires'] ?? null)
            || $setup['expires'] <= $this->clock->now()->getTimestamp() || ! is_string($setup['secret'] ?? null)
            || ! is_string($setup['fingerprint'] ?? null) || ! is_string($setup['credentials'] ?? null) || ! $user instanceof User || $state?->enabled()
            || ! hash_equals(CredentialFingerprint::from($user, $state?->revision), $setup['fingerprint'])) {
            $this->cancelSetup();

            return null;
        }
        $secret = $this->encryptor->decrypt($setup['secret'], $key);

        return ['secret' => $secret, 'uri' => $this->totp->provisioningUri($secret, $user->getUsername()), 'revision' => $setup['revision'], 'encryptedSecret' => $setup['secret'], 'fingerprint' => $setup['fingerprint'], 'credentials' => $setup['credentials']];
    }

    public function cancelSetup() : void
    {
        Session::delete(self::SETUP);
    }

    public function confirm(int $userId, Key $key, string $code) : TwoFactorChange
    {
        $setup = $this->setup($userId, $key);

        if ($setup === null) {
            throw InvalidParameterException::invalidFieldValue('expired authenticator setup; start again');
        }
        $this->reserveAttempt($userId);
        $counter = $this->totp->match($setup['secret'], trim($code));

        if ($counter === null) {
            throw self::invalidCode();
        }
        $codes = $this->recovery->generate();
        $state = $this->store->configure($userId, $setup['revision'], $setup['encryptedSecret'], $counter, array_map($this->recovery->hash(...), $codes), $setup['fingerprint']);
        $this->cancelSetup();
        $this->resetAttempts($userId);

        return new TwoFactorChange($state, $codes, CredentialFingerprint::withRevision($setup['credentials'], $state->revision));
    }

    public function regenerate(int $userId, Key $key, string $password, string $code) : TwoFactorChange
    {
        [$state, $credentials] = $this->verifySettings($userId, $key, $password, $code);
        $codes = $this->recovery->generate();
        $changed = $this->store->configure($userId, $state->revision, $state->encryptedSecret, $state->lastCounter, array_map($this->recovery->hash(...), $codes), CredentialFingerprint::withRevision($credentials, $state->revision));
        $this->resetAttempts($userId);

        return new TwoFactorChange($changed, $codes, CredentialFingerprint::withRevision($credentials, $changed->revision));
    }

    public function disable(int $userId, Key $key, string $password, string $code) : TwoFactorChange
    {
        [$state, $credentials] = $this->verifySettings($userId, $key, $password, $code);
        // Retain a fresh disabled revision: older password-only sessions stay revoked.
        $changed = $this->store->configure($userId, $state->revision, null, null, [], CredentialFingerprint::withRevision($credentials, $state->revision));
        $this->resetAttempts($userId);
        $this->cancelSetup();

        return new TwoFactorChange($changed, [], CredentialFingerprint::withRevision($credentials, $changed->revision));
    }

    private function verifySettings(int $userId, Key $key, string $password, string $code) : array
    {
        $user = $this->verifyPassword($userId, $password);
        $credentials = CredentialFingerprint::credentials($user->getPassword(), $user->getEncryptionKey(), $user->getPrivilegeLevel());
        $state = $this->store->find($userId);

        if ($state === null || ! $state->enabled() || ! $this->verifyProof($state, $key, $code)) {
            throw self::invalidCode();
        }

        return [$state, $credentials];
    }

    private function verifyPassword(int $userId, string $password) : User
    {
        $this->reserveAttempt($userId);
        $user = $this->users->getById($userId);

        if ( ! $user instanceof User || ! password_verify($password, $user->getPassword())) {
            throw InvalidArgumentException::incorrectPassword();
        }

        return $user;
    }

    public function verifyProof(TwoFactorState $state, Key $key, string $code) : bool
    {
        $this->reserveAttempt($state->userId);

        if ( ! $state->enabled() || strlen($code) > 128) {
            return false;
        }
        $hash = $this->recovery->hash($code);

        if ($hash !== null) {
            return $this->store->consumeRecovery($state->userId, $state->revision, $hash);
        }

        try {
            $secret = $this->encryptor->decrypt($state->encryptedSecret, $key);
            $counter = $this->totp->match($secret, trim($code));
        } catch (WrongKeyOrModifiedCiphertextException) {
            return false;
        }

        return $counter !== null && $this->store->consumeCounter($state->userId, $state->revision, $counter);
    }

    private function reserveAttempt(int $userId) : void
    {
        $identity = 'user:' . $userId;
        $this->attempts->reserve('two-factor-account', $identity);
        $this->attempts->reserve('two-factor-peer', $identity . "\0" . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public function resetAttempts(int $userId) : void
    {
        $identity = 'user:' . $userId;
        $this->attempts->reset('two-factor-account', $identity);
        $this->attempts->reset('two-factor-peer', $identity . "\0" . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    public static function invalidCode() : InvalidParameterException
    {
        return InvalidParameterException::invalidFieldValue('authenticator or recovery code; use a fresh code');
    }
}
