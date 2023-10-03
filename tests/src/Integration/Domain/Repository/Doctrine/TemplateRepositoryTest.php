<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;

it('should get template by id', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();

    $expectedTemplate = testKit()->testDbPopulator()->createNewTemplate()
        ->withUser($user)
        ->withCategory($category)
        ->save();

    $expectedTemplateId = $expectedTemplate->getId()->toString();

    $repository = testKit()->getService(TemplateRepositoryInterface::class);
    $actualTemplate = $repository->getById($expectedTemplateId);

    expect($actualTemplate->getId()->toString())->toBe($expectedTemplateId);
});

it('should save a template to db', function (): void {
    $expectedUser = testKit()->testDbPopulator()->createNewUser()->save();
    $expectedCategory = testKit()->testDbPopulator()->createNewCategory()->withUser($expectedUser)->save();

    $expectedTemplate = new Template();
    $expectedTemplate->setTitle('Sample Template')
        ->setContent('This is a sample content')
        ->setUser($expectedUser)
        ->setCategory($expectedCategory);

    $repository = testKit()->getService(TemplateRepositoryInterface::class);
    $repository->queueForSaving($expectedTemplate)->saveChanges();

    $row = testKit()->testOrm()->fetchOneAssoc("SELECT * FROM Template WHERE id = '{$expectedTemplate->getId()->toString()}'");

    expect($row['id'])->toBe($expectedTemplate->getId()->toString())
        ->and($row['title'])->toBe($expectedTemplate->getTitle())
        ->and($row['content'])->toBe($expectedTemplate->getContent())
        ->and($row['userId'])->toBe($expectedUser->getId()->toString())
        ->and($row['categoryId'])->toBe($expectedCategory->getId()->toString());
});

it('should remove a template from db', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();
    $template = testKit()->testDbPopulator()->createNewTemplate()->withUser($user)->withCategory($category)->save();

    $templates = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Template');
    expect($templates)->toHaveCount(1);

    $repository = testKit()->getService(TemplateRepositoryInterface::class);
    $repository->queueForRemoval($template)->saveChanges();

    $templates = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Template');
    expect($templates)->toHaveCount(0);
});
