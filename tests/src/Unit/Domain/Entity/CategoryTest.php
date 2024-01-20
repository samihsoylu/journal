<?php

declare(strict_types=1);

use Ramsey\Uuid\Rfc4122\UuidV4;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;

it('should validate required properties', function (): void {
    $category = new Category();

    // Set up required properties except 'position'
    $category->setName('Test Category');
    $category->setDescription('This is a test description');
    $category->setUser(new User());

    $category->checkErrors();
})->throws(LogicException::class);

it('should not throw an exception if all required properties are provided', function (): void {
    $category = new Category();

    // Set up all required properties
    $category->setName('Test Category');
    $category->setDescription('This is a test description');
    $category->setPosition(1);
    $category->setUser(new User());

    $category->checkErrors();
})->throwsNoExceptions();

it('should correctly get and set user', function (): void {
    $category = new Category();

    $user = new User();
    $category->setUser($user);

    expect($category->getUser())->toBe($user);
});

it('should correctly get and set name', function (): void {
    $category = new Category();
    $category->setName('Test Category');

    expect($category->getName())->toBe('Test Category');
});

it('should correctly get and set description', function (): void {
    $category = new Category();
    $category->setDescription('This is a test description');

    expect($category->getDescription())->toBe('This is a test description');
});

it('should correctly get and set position', function (): void {
    $category = new Category();
    $category->setPosition(1);

    expect($category->getPosition())->toBe(1);
});

it('should throw when category does not belong to user when asserting that it belongs to a user', function (): void {
    $user = Mockery::mock(User::class);
    $user->shouldReceive('getId')
        ->andReturn(UuidV4::uuid4());

    $category = new Category();
    $category->setUser($user);

    $anotherUser = Mockery::mock(User::class);
    $anotherUser->shouldReceive('getId')
        ->andReturn(UuidV4::uuid4());

    $category->assertBelongsToUser($anotherUser);
})->throws(LogicException::class);

it('should throw when a category belongs to a user that does not have a user id when asserting that it belongs to a user', function (): void {
    $user = Mockery::mock(User::class);
    $user->shouldReceive('getId')
        ->andReturn(null);

    $category = new Category();
    $category->setUser($user);

    $anotherUser = Mockery::mock(User::class);
    $anotherUser->shouldReceive('getId')
        ->andReturn(UuidV4::uuid4());

    $category->assertBelongsToUser($anotherUser);
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessage('This category belongs to a user that does not have an id');

it('should throw when provided user does not have an id while asserting that it belongs to a user', function (): void {
    $user = Mockery::mock(User::class);
    $user->shouldReceive('getId')
        ->andReturn(null);

    $category = new Category();
    $category->setUser($user);

    $anotherUser = Mockery::mock(User::class);
    $anotherUser->shouldReceive('getId')
        ->andReturn(UuidV4::uuid4());

    $category->assertBelongsToUser($anotherUser);
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessage('This category belongs to a user that does not have an id');
