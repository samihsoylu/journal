<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Entity\Category;

it('should correctly set and get user', function () {
    $entry = new Entry();
    $fakeUser = new User();

    $entry->setUser($fakeUser);

    expect($entry->getUser())->toBe($fakeUser);
});

it('should correctly set and get category', function () {
    $entry = new Entry();
    $fakeCategory = new Category();

    $entry->setCategory($fakeCategory);

    expect($entry->getCategory())->toBe($fakeCategory);
});

it('should correctly set and get title', function () {
    $entry = new Entry();

    $entry->setTitle('Test Title');

    expect($entry->getTitle())->toBe('Test Title');
});

it('should correctly set and get content', function () {
    $entry = new Entry();

    $entry->setContent('Test Content');

    expect($entry->getContent())->toBe('Test Content');
});

it('should throw logicexception if required properties are not set', function () {
    $entry = new Entry();

    $entry->checkErrors();
})->throws(LogicException::class);

it('should not throw any exceptions when all required properties are set', function () {
    $entry = new Entry();
    $fakeUser = new User();
    $fakeCategory = new Category();

    $entry->setUser($fakeUser);
    $entry->setCategory($fakeCategory);
    $entry->setTitle('Test Title');
    $entry->setContent('Test Content');

    $entry->checkErrors();
})->throwsNoExceptions();