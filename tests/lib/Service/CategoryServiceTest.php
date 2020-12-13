<?php

namespace Tests\Service;

use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Utility\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private $categoryRepository;

    private $entryRepository;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entryRepository = $this->createMock(EntryRepository::class);

        Registry::set(CategoryRepository::class, $this->categoryRepository);
        Registry::set(EntryRepository::class, $this->entryRepository);
    }

    public function x_testGetCategoryById(): void
    {
        // @todo: ..cant test because static methods inside of UserSession cannot be mocked.
    }

    public function testGetCategoryByIdNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->categoryRepository->expects(self::once())
            ->method('getById')
            ->willReturn(null);

        $service = new CategoryService();
        $service->getCategoryById(5);
    }

    public function x_testGetCategoryByIdDoesNotBelongToUserNotFoundException(): void
    {
        // @todo cant test because static methods inside of UserSession cannot be mocked.
        $this->expectException(NotFoundException::class);

        $this->categoryRepository->expects(self::once())
            ->method('getById')
            ->willReturn(null);
    }
}