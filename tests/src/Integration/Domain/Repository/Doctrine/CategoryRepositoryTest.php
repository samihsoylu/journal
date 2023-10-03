<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;

it('should get category by id', function (): void {
    $expectedAssignedUser = testKit()->testDbPopulator()->createNewUser()
        ->save();

    $expectedCategory = testKit()->testDbPopulator()->createNewCategory()
        ->withUser($expectedAssignedUser)
        ->save();

    $expectedCategoryId = $expectedCategory->getId()->toString();
    $expectedAssignedUserId = $expectedAssignedUser->getId()->toString();

    $categoryRepository = testKit()->getService(CategoryRepositoryInterface::class);
    $actualCategory = $categoryRepository->getById($expectedCategoryId);

    $actualCategoryId = $actualCategory->getId()->toString();
    $actualAssignedUserId = $actualCategory->getUser()->getId()->toString();

    expect($actualCategoryId)->toBe($expectedCategoryId)
        ->and($actualAssignedUserId)->toBe($expectedAssignedUserId);
});

it('should save a category to db', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()
        ->save();

    $repository = testKit()->getService(CategoryRepositoryInterface::class);

    $expectedCategory = new Category();
    $expectedCategory->setName('Food')
        ->setDescription('Food notes')
        ->setPosition(1)
        ->setUser($user);

    $repository->queueForSaving($expectedCategory)
        ->saveChanges();

    $row = testKit()->testOrm()
        ->fetchOneAssoc("SELECT * FROM Category WHERE userId = '{$user->getId()->toString()}'");

    expect($row['id'])->toBe($expectedCategory->getId()->toString())
        ->and($row['name'])->toBe($expectedCategory->getName())
        ->and($row['description'])->toBe($expectedCategory->getDescription());
});

it('should remove a category from db', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()
        ->withUser($user)
        ->save();

    $categories = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Category');
    expect($categories)->toHaveCount(1);

    $repository = testKit()->getService(CategoryRepositoryInterface::class);
    $repository->queueForRemoval($category)
        ->saveChanges();

    $categories = testKit()->testOrm()->fetchAllAssoc('SELECT * FROM Category');
    expect($categories)->toHaveCount(0);
});
