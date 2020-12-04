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

    public function testGetCategoryById(): void
    {

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

    public function testGetCategoryByIdDoesNotBelongToUserNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->categoryRepository->expects(self::once())
            ->method('getById')
            ->willReturn(null);
    }
}