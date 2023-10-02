<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\EntryRepositoryInterface;

it('should get entry by id', function () {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();

    $expectedEntry = testKit()->testDbPopulator()->createNewEntry()
        ->withUser($user)
        ->withCategory($category)
        ->save();

    $expectedEntryId = $expectedEntry->getId()->toString();

    $repository = testKit()->getService(EntryRepositoryInterface::class);
    $actualEntry = $repository->getById($expectedEntryId);

    expect($actualEntry->getId()->toString())->toBe($expectedEntryId);
});

it('should save an entry to db', function () {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();

    $expectedEntry = new Entry();
    $expectedEntry->setUser($user)
        ->setCategory($category)
        ->setTitle('Entry Title')
        ->setContent('Entry content');

    $repository = testKit()->getService(EntryRepositoryInterface::class);
    $repository->queueForSaving($expectedEntry)
        ->saveChanges();

    $row = testKit()->testOrm()->fetchOneAssoc("SELECT * FROM Entry WHERE id = '{$expectedEntry->getId()->toString()}'");

    expect($row['id'])->toBe($expectedEntry->getId()->toString())
        ->and($row['title'])->toBe($expectedEntry->getTitle())
        ->and($row['content'])->toBe($expectedEntry->getContent());
});

it('should remove an entry from db', function () {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();
    $entry = testKit()->testDbPopulator()->createNewEntry()
        ->withUser($user)
        ->withCategory($category)
        ->save();

    $entries = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Entry');
    expect($entries)->toHaveCount(1);

    $repository = testKit()->getService(EntryRepositoryInterface::class);
    $repository->queueForRemoval($entry)
        ->saveChanges();

    $entries = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Entry');
    expect($entries)->toHaveCount(0);
});