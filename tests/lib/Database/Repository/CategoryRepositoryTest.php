<?php declare(strict_types=1);

namespace Tests\Database\Repository;

use App\Database\Model\Category;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    private array $mockCategories;

    public function setUp(): void
    {
        $categories = ['Dreams', 'Diary', 'Food', 'Work', 'Personal'];
        for ($i = 0; $i < 5; $i++) {
            $this->mockCategories[$i] = $this->createConfiguredMock(Category::class, [
                'getId' => $i,
                'getName' => $categories[$i],
            ]);
        }
    }

    /**
     * In case null is returned, $categoryRepository->getById() must throw an exception
     */
    public function testGetByIdNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $mockEntityManager = $this->createMockEntityManager('find', null);

        $repository = new CategoryRepository($mockEntityManager);

        // This will throw a NotFoundException
        $repository->getById(12345);
    }

    /**
     * In case the category is found, the $categoryRepository->getByName() must return a category
     */
    public function testGetByName(): void
    {
        $expectedId = 3;
        $mockCategory = $this->mockCategories[$expectedId];

        // Specifies findBy will return final result
        $mockEntityRepository = $this->createMockEntityRepository(
            'findBy',
            ['name' => 'Food'],
            [0 => $mockCategory]
        );

        // Specifies that getRepository will return EntityRepository so that ->findBy() can be chained
        $mockEntityManager = $this->createMockEntityManager('getRepository', $mockEntityRepository);

        $repository = new CategoryRepository($mockEntityManager);
        $category = $repository->getByName('Food');

        $this->assertEquals($expectedId, $category->getId());
    }

    /**
     * In case an empty array is returned, $categoryRepository->getByName() must throw an exception
     */
    public function testGetByNameNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        // Specifies findBy will return final result
        $mockEntityRepository = $this->createMockEntityRepository('findBy', ['name' => 'Ideas'], []);

        // Specifies getRepository will return EntityRepository so that ->findBy() can be chained
        $mockEntityManager = $this->createMockEntityManager('getRepository', $mockEntityRepository);

        $repository = new CategoryRepository($mockEntityManager);

        // This will throw a NotFoundException
        $repository->getByName('Ideas');
    }

    public function testGetAllCategoriesForUser(): void
    {
        $expectedUsername = 'michael';
        $expectedPassword = 'm!ch4el#1';
        $expectedEmail = 'm.scott@mail.io';
        $expectedPrivilegeLevel = User::PRIVILEGE_LEVEL_ADMIN;

        $user = new User();
        $user->setUsername($expectedUsername)
            ->setPassword($expectedPassword)
            ->setEmailAddress($expectedEmail)
            ->setPrivilegeLevel($expectedPrivilegeLevel);
        $user->setLastUpdatedTimestamp();

        // Specifies findBy will return final result
        $mockEntityRepository = $this->createMockEntityRepository('findBy', ['referencedUser' => $user], []);

        // Specifies getRepository will return EntityRepository so that ->findBy() can be chained
        $mockEntityManager = $this->createMockEntityManager('getRepository', $mockEntityRepository);

        $repository = new CategoryRepository($mockEntityManager);
        $categories = $repository->getAllCategoriesForUser($user);

        $this->assertIsArray($categories);
        $this->assertCount(0, $categories);
    }

    private function createMockEntityManager(string $hasMethod, $methodWillReturn): EntityManager
    {
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->expects($this->once())
            ->method($hasMethod)
            ->willReturn($methodWillReturn);

        return $mockEntityManager;
    }

    private function createMockEntityRepository(string $hasMethod, array $withExpectedParameters, $methodWillReturn): EntityRepository
    {
        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $mockEntityRepository->expects($this->once())
            ->method($hasMethod)
            ->with($withExpectedParameters)
            ->willReturn($methodWillReturn);

        return $mockEntityRepository;
    }
}