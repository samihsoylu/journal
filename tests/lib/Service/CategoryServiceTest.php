<?php

namespace Tests\Service;

use App\Database\Model\Category;
use App\Database\Model\Entry;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Utility\Registry;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private $categoryRepository;
    private $entryRepository;
    private $userRepository;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entryRepository    = $this->createMock(EntryRepository::class);
        $this->userRepository     = $this->createMock(UserRepository::class);

        Registry::set(CategoryRepository::class, $this->categoryRepository);
        Registry::set(EntryRepository::class, $this->entryRepository);
        Registry::set(UserRepository::class, $this->userRepository);
    }

    /**
     * @return Category|MockObject
     */
    private function getMockCategory(User $user,?int $categoryId): Category
    {
        $mock = $this->createMock(Category::class);
        $mock->method('getReferencedUser')
            ->willReturn($user);

        if ($categoryId !== null) {
            $mock->method('getId')
                ->willReturn($categoryId);
        }

        return $mock;
    }

    /**
     * @return Category[]|MockObject[]
     */
    private function getMockCategories(User $user): array
    {
        $mockCategories = [];

        for ($i = 0; $i < 5; $i++) {
            $mock = $this->createMock(Category::class);
            $mock->method('getReferencedUser')
                ->willReturn($user);

            $mockCategories[] = $mock;
        }

        return $mockCategories;
    }

    /**
     * @return User|MockObject
     */
    private function getMockUser(int $userId): User
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);

        return $mockUser;
    }

    private function getMockEntries(User $user, Category $category): array
    {
        $mockEntries = [];
        for ($i = 0; $i < 5; $i++) {
            $mock = $this->createMock(Entry::class);
            $mock->method('getReferencedCategory')
                ->willReturn($category);
            $mock->method('getReferencedUser')
                ->willReturn($user);

            $mockEntries[] = $mock;
        }
        return $mockEntries;
    }

    /**
     * Tests that all categories are returned in an array.
     *
     * @return void
     */
    public function testGetAllCategoriesForUser(): void
    {
        $userId = 190;
        $mockUser = $this->getMockUser($userId);
        $mockCategories = $this->getMockCategories($mockUser);

        $this->userRepository->expects(self::once())
            ->method('getById')
            ->with($userId)
            ->willReturn($mockUser);

        $this->categoryRepository->expects(self::once())
            ->method('findByUser')
            ->with($mockUser)
            ->willReturn($mockCategories);

        $service    = new CategoryService();
        $categories = $service->getAllCategoriesForUser($userId);

        $this->assertCount(count($mockCategories), $categories);
    }

    /**
     * Tests whether an exception is thrown if a user does not exist when making this resource call
     *
     * @return void
     */
    public function testGetAllCategoriesForUserNotFoundUserException(): void
    {
        $this->expectException(NotFoundException::class);
        $userId = 190;

        $this->userRepository->expects(self::once())
            ->method('getById')
            ->with($userId)
            ->willReturn(null);

        $service = new CategoryService();
        $service->getAllCategoriesForUser($userId);
    }

    /**
     * Tests the retrieval of an individual category, the user is found and category is too, this throws no exceptions
     *
     * @return void
     */
    public function testGetCategoryForUser(): void
    {
        $categoryId = 3910;
        $userId = 41034;

        $mockUser = $this->getMockUser($userId);

        $mockCategory = $this->getMockCategory($mockUser, $categoryId);

        $this->categoryRepository
            ->method('getById')
            ->with($categoryId)
            ->willReturn($mockCategory);

        $service = new CategoryService();
        $category = $service->getCategoryForUser($categoryId, $userId);

        $this->assertEquals($category->getId(), $categoryId);
        $this->assertEquals($category->getReferencedUser()->getId(), $userId);
    }

    /**
     * Tests if the category is created properly, one queue() call and one save() call.
     *
     * @return void
     */
    public function testCreateCategory(): void
    {
        $userId = 5;
        $mockUser = $this->getMockUser($userId);

        $this->userRepository->expects(self::once())
            ->method('getById')
            ->with($userId)
            ->willReturn($mockUser);

        // Asserts that queued object must be an instance of Category
        $this->categoryRepository->expects(self::once())
            ->method('queue')
            ->with(
                $this->callback(
                    function ($model) {
                        return ($model instanceof Category);
                    }
                )
            );

        $this->categoryRepository->expects(self::once())
            ->method('save');

        $service = new CategoryService();
        $service->createCategory($userId, 'Random title', 'Random description');
    }

    /**
     * Tests that the unique constraint exception is catched and handled properly
     *
     * @return void
     */
    public function testCreateCategoryUniqueConstraintException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $userId = 5;
        $mockUser = $this->getMockUser($userId);

        $this->userRepository->expects(self::once())
            ->method('getById')
            ->with($userId)
            ->willReturn($mockUser);

        $this->categoryRepository->expects(self::once())
            ->method('save')
            ->willThrowException(
                new UniqueConstraintViolationException(
                    'Category',
                    new PDOException(
                        new \PDOException('')
                    )
                )
            );

        $service = new CategoryService();
        $service->createCategory($userId, 'Random title', 'Random description');
    }

    /**
     * Tests that the queue and save methods are invoked when updating a category
     *
     * @return void
     */
    public function testUpdateCategory(): void
    {
        $userId = 3;
        $categoryId = 5;

        $mockUser = $this->getMockUser($userId);
        $mockCategory = $this->getMockCategory($mockUser, $categoryId);

        $this->categoryRepository
            ->method('getById')
            ->with($categoryId)
            ->willReturn($mockCategory);

        // Asserts that queued object must be an instance of Category
        $this->categoryRepository->expects(self::once())
            ->method('queue')
            ->with(
                $this->callback(
                    function ($model) {
                        return ($model instanceof Category);
                    }
                )
            );

        $this->categoryRepository->expects(self::once())
            ->method('save');

        $service = new CategoryService();
        $service->updateCategory($userId, $categoryId, 'New Category Name', 'New Category Description');
    }

    public function testDeleteCategoryAndAssociatedEntries(): void
    {
        $userId = 5;
        $categoryId = 10;
        $mockUser = $this->getMockUser($userId);
        $mockCategory = $this->getMockCategory($mockUser, $categoryId);
        $mockEntries = $this->getMockEntries($mockUser, $mockCategory);
        $entriesCount = count($mockEntries);

        $this->categoryRepository
            ->method('getById')
            ->with($categoryId)
            ->willReturn($mockCategory);

        $this->entryRepository
            ->method('findByUserIdAndCategoryId')
            ->willReturn($mockEntries);

        // + 1 for removing category
        $this->categoryRepository->expects(self::exactly($entriesCount + 1))
            ->method('remove')
            ->withConsecutive(
                [$this->identicalTo($mockEntries[0])],
                [$this->identicalTo($mockEntries[1])],
                [$this->identicalTo($mockEntries[2])],
                [$this->identicalTo($mockEntries[3])],
                [$this->identicalTo($mockEntries[4])],
                [$this->identicalTo($mockCategory)]
            );

        $this->categoryRepository->expects(self::once())
            ->method('save');

        $service = new CategoryService();
        $service->deleteCategoryAndAssociatedEntries($categoryId, $userId);
    }
}
