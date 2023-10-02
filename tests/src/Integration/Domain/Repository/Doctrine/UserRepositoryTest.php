<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmTransactionInterface;

it('should get user by id', function () {
    $expectedUsername = 'jacob';

    $expectedId = testKit()->testDbPopulator()->createNewUser()
        ->withUsername($expectedUsername)
        ->save()
        ->getId();

    $repository = testKit()->getService(UserRepositoryInterface::class);
    $actualUser = $repository->getById($expectedId->toString());

    expect($actualUser->getId())->toBe($expectedId)
        ->and($actualUser->getUsername())->toBe($expectedUsername);
});

it('should save a user to db', function() {
    $expectedUser = new User();
    $expectedUser->setUsername('samih')
        ->setPassword('securePassword')
        ->setEmailAddress('email@example.com')
        ->setProtectedKey('key')
        ->setRole(Role::USER);

    $repository = testKit()->getService(UserRepositoryInterface::class);
    $repository->queueForSaving($expectedUser)
        ->saveChanges();

    $row = testKit()->testOrm()->fetchOneAssoc("SELECT * FROM User WHERE id = '{$expectedUser->getId()->toString()}'");

    expect($row['id'])->toBe($expectedUser->getId()->toString())
        ->and($row['username'])->toBe($expectedUser->getUsername())
        ->and($row['password'])->toBe($expectedUser->getPassword())
        ->and($row['emailAddress'])->toBe($expectedUser->getEmailAddress())
        ->and($row['protectedKey'])->toBe($expectedUser->getProtectedKey())
        ->and($row['role'])->toBe($expectedUser->getRole()->value);
});

it('should remove a user from db', function() {
    $expectedUser = testKit()->testDbPopulator()->createNewUser()->save();

    $rows = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM User');
    expect($rows)->toHaveCount(1);

    $repository = testKit()->getService(UserRepositoryInterface::class);
    $repository->queueForRemoval($expectedUser)
        ->saveChanges();

    $rows = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM User');
    expect($rows)->toHaveCount(0);
});