<?php

declare(strict_types=1);

namespace Tests\TestFramework;

use App\Framework\Kernel;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrm;
use Tests\TestFramework\TestOrm\Doctrine\DoctrineTestOrmTransaction;
use Tests\TestFramework\TestOrm\TestOrmInterface;
use Tests\TestFramework\TestOrm\TestOrmTransactionInterface;

/**
 * Base class for integration tests.
 *
 * Provides:
 * - Kernel booting and container access
 * - Database transaction management for test isolation
 * - TestOrm access for raw SQL assertions
 * - Service and repository retrieval helpers
 *
 * Usage:
 *   class MyTest extends IntegrationTestCase
 *   {
 *       protected function setUp(): void
 *       {
 *           parent::setUp();
 *           $this->repository = $this->getRepository(MyRepository::class);
 *       }
 *
 *       #[Test]
 *       public function it_should_do_something(): void
 *       {
 *           // Test code...
 *       }
 *   }
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * Shared kernel instance (singleton per test run).
     */
    protected static ?Kernel $kernel = null;

    /**
     * TestOrm instance for database operations and assertions.
     */
    protected TestOrmInterface $testOrm;

    /**
     * Transaction manager for test isolation.
     */
    protected TestOrmTransactionInterface $transaction;

    protected function setUp() : void
    {
        parent::setUp();

        $this->bootKernel();

        // Begin transaction for test isolation
        $this->transaction->beginTransaction();

        // Set TestContext for factories
        TestContext::$testOrm = $this->testOrm;
    }

    protected function tearDown() : void
    {
        // Rollback transaction to restore database state
        $this->transaction->rollback();

        // Clear TestContext
        TestContext::$testOrm = null;

        parent::tearDown();
    }

    /**
     * Boot the application kernel (once per test run).
     */
    private function bootKernel() : void
    {
        if ( ! self::$kernel instanceof Kernel) {
            self::$kernel = new Kernel('test', true);
        }

        $container = self::$kernel->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $this->testOrm = new DoctrineTestOrm($entityManager);
        $this->transaction = new DoctrineTestOrmTransaction($entityManager);
    }

    /**
     * Get the DI container.
     */
    protected function getContainer() : Container
    {
        return self::$kernel->getContainer();
    }

    /**
     * Get a service from the DI container.
     *
     * @template T of object
     *
     * @param class-string<T> $serviceClass
     * @return T
     */
    protected function getService(string $serviceClass) : object
    {
        return $this->getContainer()->get($serviceClass);
    }

    /**
     * Get a repository from the DI container.
     *
     * @template T of object
     *
     * @param class-string<T> $repositoryClass
     * @return T
     */
    protected function getRepository(string $repositoryClass) : object
    {
        return $this->getContainer()->get($repositoryClass);
    }
}
