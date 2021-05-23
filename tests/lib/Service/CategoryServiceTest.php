<?php

namespace Tests\Service;

use App\Database\Model\Category;
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
     * @return Category[]|MockObject[]
     */
    private function getMockCategories(User $user): array
    {
        $mockCategories = [];

        for ($i = 0; $i < 5; $i++) {
            $mock = $this->createMock(Category::class);
            $mock->method('getName')->willReturn("Space {$i}");
            $mock->method('getDescription')->willReturn("Space desc {$i}");
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
        $mockUser->method('getUsername')->willReturn('username1');
        $mockUser->method('getEmailAddress')->willReturn('email1@email.com');
        $mockUser->method('getPrivilegeLevel')->willReturn(User::PRIVILEGE_LEVEL_ADMIN);

        return $mockUser;
    }

    /**
     * Tests that all categories are returned in an array and no exception is thrown if the user exists.
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
     * Tests whether an exception is thrown if a user does not when making this resource call
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

        $service    = new CategoryService();
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

        $mockCategory = $this->getMockCategories($mockUser)[0];
        $mockCategory->method('getId')->willReturn($categoryId);

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
     * Tests whether an exception is thrown if the category does not exist
     *
     * @return void
     */
    public function testGetCategoryForUserNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->categoryRepository
            ->method('getById')
            ->willReturn(null);

        $service = new CategoryService();
        $service->getCategoryForUser(5, 0);
    }

    /**
     * Tests whether an exception is thrown if the user does not exist
     *
     * @return void
     */
    public function testGetCategoryForUserNotFoundExceptionForNotMatchingUserId(): void
    {
        $this->expectException(NotFoundException::class);

        $categoryId = 1;
        $userId = 2;

        $mockUser = $this->getMockUser($userId);

        $mockCategory = $this->getMockCategories($mockUser)[0];
        $mockCategory->method('getId')->willReturn($categoryId);

        $this->categoryRepository
            ->method('getById')
            ->willReturn($mockCategory);

        $service = new CategoryService();
        $service->getCategoryForUser($categoryId, 65);
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
                    function($model) {
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

        $mockCategory = $this->getMockCategories($mockUser)[0];
        $mockCategory->method('getId')->willReturn($categoryId);

        $this->categoryRepository
            ->method('getById')
            ->with($categoryId)
            ->willReturn($mockCategory);

        // Asserts that queued object must be an instance of Category
        $this->categoryRepository->expects(self::once())
            ->method('queue')
            ->with(
                $this->callback(
                    function($model) {
                        return ($model instanceof Category);
                    }
                )
            );

        $this->categoryRepository->expects(self::once())
            ->method('save');

        $service = new CategoryService();
        $service->updateCategory($userId, $categoryId, 'New Category Name', 'New Category Description');
    }
}