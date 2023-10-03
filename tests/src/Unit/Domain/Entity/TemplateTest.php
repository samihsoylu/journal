<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;

it('should throw logic exception if required property title is missing', function (): void {
    $template = new Template();
    $template->setContent("content");
    $template->setCategory(new Category());
    $template->setUser(new User());

    $template->checkErrors();
})->throws(LogicException::class);

it('should throw logic exception if required property content is missing', function (): void {
    $template = new Template();
    $template->setTitle("title");
    $template->setCategory(new Category());
    $template->setUser(new User());

    $template->checkErrors();
})->throws(LogicException::class);

it('should throw logic exception if required property category is missing', function (): void {
    $template = new Template();
    $template->setTitle("title");
    $template->setContent("content");
    $template->setUser(new User());

    $template->checkErrors();
})->throws(LogicException::class);

it('should throw logic exception if required property user is missing', function (): void {
    $template = new Template();
    $template->setTitle("title");
    $template->setContent("content");
    $template->setCategory(new Category());
    $template->checkErrors();
})->throws(LogicException::class);

it('should not throw any exceptions when all required properties are set', function (): void {
    $fakeUser = new User();
    $fakeCategory = new Category();

    $template = new Template();
    $template->setTitle("title");
    $template->setContent("content");
    $template->setCategory($fakeCategory);
    $template->setUser($fakeUser);
    $template->checkErrors();

    expect($template->getTitle())->toBe('title')
        ->and($template->getContent())->toBe('content')
        ->and($template->getUser())->toBe($fakeUser)
        ->and($template->getCategory())->toBe($fakeCategory);
});
