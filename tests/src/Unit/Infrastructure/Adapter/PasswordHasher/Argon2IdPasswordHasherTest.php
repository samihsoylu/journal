<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\PasswordHasher\Argon2IdPasswordHasher;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\Exception\InvalidPasswordException;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;

it('should hash a password correctly', function () {
    $hasher = new Argon2IdPasswordHasher();
    $plainPassword = 'testPassword';
    $hashedPassword = $hasher->hash($plainPassword);

    expect(password_verify($plainPassword, $hashedPassword))->toBeTrue();
});

it('should verify a hashed password', function () {
    $hasher = new Argon2IdPasswordHasher();
    $plainPassword = 'testPassword';
    $hashedPassword = password_hash($plainPassword, PASSWORD_ARGON2ID);

    expect($hasher->verify($plainPassword, $hashedPassword))->toBeTrue();
});

it('should throw an exception for too long password', function () {
    $hasher = new Argon2IdPasswordHasher();
    $longPassword = str_repeat('a', PasswordHasherInterface::MAX_PASSWORD_LENGTH + 1);

    $hasher->hash($longPassword);
})->throws(InvalidPasswordException::class);