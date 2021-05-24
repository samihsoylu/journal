<?php

namespace Tests\Service\Helper;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\CategoryHelper;
use App\Utility\Registry;
use PHPUnit\Framework\TestCase;

class CategoryHelperTest extends TestCase
{
    private $categoryRepository;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);

        Registry::set(CategoryRepository::class, $this->categoryRepository);
    }

    public function testGetCategoryForUserNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->categoryRepository
            ->method('getById')
            ->willReturn(null);

        $helper = new CategoryHelper();
        $helper->getCategoryForUser(5, 5);
    }

    public function testGetCategoryForUserCategoryNotOwnedByUserException(): void
    {
        $this->expectException(NotFoundException::class);

        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn(6);

        $mockCategory = $this->createMock(Category::class);
        $mockCategory->method('getReferencedUser')->willReturn($mockUser);

        $this->categoryRepository
            ->method('getById')
            ->willReturn($mockCategory);

        $helper = new CategoryHelper();
        $helper->getCategoryForUser(5, 5);
    }
}
