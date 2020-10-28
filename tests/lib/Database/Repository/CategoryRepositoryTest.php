<?php declare(strict_types=1);

namespace Tests\Database\Repository;

use App\Database\Model\Category;
use App\Database\Repository\CategoryRepository;
use App\Exception\UserException\NotFoundException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CategoryRepositoryTest extends TestCase
{
    /**
     * In case the category is found, the $repository->getById() must return a valid category instance
     */
    public function testGetById(): void
    {
        $expectedId = 64;
        $expectedName = 'Office Supplies';

        // We specify here that the mock category class will return 64 when using $category->getId();
        $mockCategory = $this->createConfiguredMock(Category::class, [
            'getId' => $expectedId,
            'getName' => $expectedName,
        ]);

        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->expects($this->once())
            ->method('find')
            ->willReturn($mockCategory);

        $repository = new CategoryRepository($mockEntityManager);

        /** @var Category $category */
        $category = $repository->getById($expectedId);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals($expectedId, $category->getId());
        $this->assertEquals($expectedName, $category->getName());
    }

    /**
     * In case the category is found, the $repository->getByName() must return a category
     */
    public function testGetByName(): void
    {
        $expectedId = 50;

        // We specify here that the mock category class will return 50 when using $category->getId();
        $mockCategory = $this->createConfiguredMock(Category::class, ['getId' => $expectedId]);

        // $mockEntityManager and $mockEntityRepository together mock the $this->db->getRepository->findBy() chain in
        // CategoryRepository.
        // Specifies findBy will return final result
        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $mockEntityRepository->expects($this->once())
            ->method('findBy')
            ->with(['name' => 'Dreams'])
            ->willReturn([0 => $mockCategory]);

        // Specifies that getRepository will return EntityRepository so that ->findBy() can be chained
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockEntityRepository);

        $repository = new CategoryRepository($mockEntityManager);
        $category = $repository->getByName('Dreams');

        $this->assertEquals($expectedId, $category->getId());
    }

    /**
     * In case an empty array is returned, $repository->getByName() must throw an exception
     */
    public function testGetByNameNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        // $mockEntityManager and $mockEntityRepository together mock the $this->db->getRepository->findBy() chain in
        // CategoryRepository.
        // Specifies findBy will return final result
        $mockEntityRepository = $this->createMock(EntityRepository::class);
        $mockEntityRepository->expects($this->once())
            ->method('findBy')
            ->with(['name' => 'Dreams'])
            ->willReturn([]);

        // Specifies getRepository will return EntityRepository so that ->findBy() can be chained
        $mockEntityManager = $this->createMock(EntityManager::class);
        $mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockEntityRepository);

        $repository = new CategoryRepository($mockEntityManager);

        // This will throw a NotFoundException
        $repository->getByName('Dreams');
    }
}