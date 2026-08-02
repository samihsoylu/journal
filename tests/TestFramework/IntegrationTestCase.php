<?php

declare(strict_types=1);

namespace Tests\TestFramework;

use App\Framework\Kernel;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tests\TestFramework\TestOrm\TestOrm;
use Tests\TestFramework\TestOrm\TestOrmTransaction;

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
    protected static ?Kernel $kernel = null;
    protected ContainerInterface $container;
    protected TestOrm $testOrm;
    private TestOrmTransaction $transaction;

    protected function setUp() : void
    {
        parent::setUp();

        $this->bootKernel();

        $this->testOrm = $this->container->get(TestOrm::class);
        $this->transaction = $this->container->get(TestOrmTransaction::class);

        // Begin transaction for test isolation
        $this->transaction->beginTransaction();

        // Set TestContext for factories
        TestContext::$testOrm = $this->testOrm;
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        // Rollback transaction to restore database state
        $this->transaction->rollback();

        // Clear TestContext
        TestContext::$testOrm = null;
    }

    /**
     * Boot the application kernel (once per test run).
     *
     * If the EntityManager is closed (e.g., after a database error),
     * the kernel is reset to get a fresh EntityManager.
     */
    private function bootKernel() : void
    {
        if (self::$kernel instanceof Kernel) {
            $em = self::$kernel->getContainer()->get(EntityManagerInterface::class);

            if ($em->isOpen()) {
                $this->container = self::$kernel->getContainer();

                return;
            }
            // EntityManager is closed, reset kernel to get a fresh one
            self::$kernel = null;
        }

        self::$kernel = new Kernel('test', true);
        $this->container = self::$kernel->getContainer();
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
