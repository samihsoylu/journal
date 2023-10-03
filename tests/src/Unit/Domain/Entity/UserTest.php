<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;

it('should correctly set and get username', function (): void {
    $user = new User();
    $user->setUsername('samih');

    expect($user->getUsername())->toBe('samih');
});

it('should correctly set and get password', function (): void {
    $user = new User();
    $user->setPassword('securePassword');

    expect($user->getPassword())->toBe('securePassword');
});

it('should correctly set and get email address', function (): void {
    $user = new User();
    $user->setEmailAddress('email@example.com');

    expect($user->getEmailAddress())->toBe('email@example.com');
});

it('should correctly set and get role', function (): void {
    $user = new User();
    $user->setRole(Role::USER);

    expect($user->getRole())->toBe(Role::USER);
});

it('should correctly set and get protected key', function (): void {
    $user = new User();
    $user->setProtectedKey('key');

    expect($user->getProtectedKey())->toBe('key');
});

it('should correctly set and get preferred timezone', function (): void {
    $user = new User();
    $user->setPreferredTimezone('UTC');

    expect($user->getPreferredTimezone())->toBe('UTC');
});

it('should throw LogicException if required properties are missing on PrePersist', function (): void {
    $user = new User();

    $user->checkErrors();
})->throws(LogicException::class);

it('should not throw exception if all required properties are set on PrePersist', function (): void {
    $user = new User();
    $user->setUsername('samih');
    $user->setPassword('securePassword');
    $user->setEmailAddress('email@example.com');
    $user->setProtectedKey('key');
    $user->setRole(Role::USER);

    $user->checkErrors();
})->throwsNoExceptions();
