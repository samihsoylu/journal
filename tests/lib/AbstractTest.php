<?php declare(strict_types=1);

namespace Tests;

use App\Database\Model\Category;
use App\Database\Model\Entry;
use App\Database\Model\Template;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\TemplateRepository;
use App\Database\Repository\UserRepository;
use App\Utility\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractTest extends TestCase
{
    protected CategoryRepository $categoryRepository;
    protected EntryRepository $entryRepository;
    protected UserRepository $userRepository;
    protected TemplateRepository $templateRepository;

    public function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entryRepository    = $this->createMock(EntryRepository::class);
        $this->userRepository     = $this->createMock(UserRepository::class);
        $this->templateRepository = $this->createMock(TemplateRepository::class);

        Registry::set(CategoryRepository::class, $this->categoryRepository);
        Registry::set(EntryRepository::class, $this->entryRepository);
        Registry::set(UserRepository::class, $this->userRepository);
        Registry::set(TemplateRepository::class, $this->templateRepository);
    }

    /**
     * @return Category|MockObject
     */
    protected function getMockCategory(User $user, ?int $categoryId): Category
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
    protected function getMockCategories(User $user): array
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
    protected function getMockUser(int $userId): User
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);

        return $mockUser;
    }

    protected function getMockEntries(User $user, Category $category): array
    {
        $mockEntries = [];
        for ($i = 0; $i < 5; $i++) {
            $mock = $this->createMock(Entry::class);
            $mock->method('getId')
                ->willReturn($i + 1);
            $mock->method('getReferencedCategory')
                ->willReturn($category);
            $mock->method('getReferencedUser')
                ->willReturn($user);

            $mockEntries[] = $mock;
        }
        return $mockEntries;
    }

    protected function getMockTemplates(User $user, Category $category): array
    {
        $mockTemplates = [];
        for ($i = 0; $i < 5; $i++) {
            $mock = $this->createMock(Template::class);
            $mock->method('getId')
                ->willReturn($i + 1);
            $mock->method('getReferencedCategory')
                ->willReturn($category);
            $mock->method('getReferencedUser')
                ->willReturn($user);

            $mockTemplates[] = $mock;
        }
        return $mockTemplates;
    }


    protected function setMockUser(int $userId): User
    {
        $mockUser = $this->getMockUser($userId);

        $this->userRepository->method('getById')
            ->with($userId)
            ->willReturn($mockUser);

        return $mockUser;
    }

    protected function setMockCategory(User $mockUser, int $categoryId): Category
    {
        $mockCategory = $this->getMockCategory($mockUser, $categoryId);

        $this->categoryRepository
            ->method('getById')
            ->with($categoryId)
            ->willReturn($mockCategory);

        return $mockCategory;
    }

    protected function setMockEntries(User $mockUser, Category $mockCategory): array
    {
        $mockEntries = $this->getMockEntries($mockUser, $mockCategory);

        $this->entryRepository
            ->method('findByUserIdAndCategoryId')
            ->willReturn($mockEntries);
        $this->entryRepository
            ->method('findByUser')
            ->willReturn($mockEntries);

        return $mockEntries;
    }

    protected function setMockTemplates(User $mockUser, Category $mockCategory): array
    {
        $mockTemplates = $this->getMockTemplates($mockUser, $mockCategory);

        $this->templateRepository->method('findByUserIdAndCategoryId')
            ->willReturn($mockTemplates);

        return $mockTemplates;
    }
}
