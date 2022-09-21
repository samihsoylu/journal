<?php declare(strict_types=1);

namespace Tests\Service;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Service\Helper\CategoryHelper;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Tests\AbstractTest;

class CategoryServiceTest extends AbstractTest
{
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

        $helper = new CategoryHelper();
        $category = $helper->getCategoryForUser($categoryId, $userId);

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
        $service->createCategory($userId, 'Random name', 'Random description');
    }

    /**
     * Tests that the unique constraint exception is caught and handled properly, this exception occurs when a category
     * with the name exists already in the database.
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
                    new Exception('Category'),
                    null,
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
}
