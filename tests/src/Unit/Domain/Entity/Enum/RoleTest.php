<?php

use SamihSoylu\Journal\Domain\Entity\Enum\Role;

it('should correctly detect if the role is OWNER', function () {
    $role = Role::OWNER;
    expect($role->isOwner())->toBeTrue()
        ->and($role->isAdmin())->toBeFalse()
        ->and($role->isUser())->toBeFalse();
});

it('should correctly detect if the role is ADMIN', function () {
    $role = Role::ADMIN;
    expect($role->isOwner())->toBeFalse()
        ->and($role->isAdmin())->toBeTrue()
        ->and($role->isUser())->toBeFalse();
});

it('should correctly detect if the role is USER', function () {
    $role = Role::USER;
    expect($role->isOwner())->toBeFalse()
        ->and($role->isAdmin())->toBeFalse()
        ->and($role->isUser())->toBeTrue();
});

