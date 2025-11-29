<?php

declare(strict_types=1);

namespace App\Database;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Exception;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

final readonly class Database
{
    private DependencyFactory $dependencyFactory;
    private EntityManager $entityManager;

    public function __construct()
    {
        $dbParams = [
            'driver' => 'pdo_mysql',
            'host' => $_ENV['DB_HOST'],
            'user' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'dbname' => $_ENV['DB_SCHEMA'],
        ];

        // Doctrine ORM 3.x uses PSR-6 cache directly (no need for DoctrineProvider wrapper)
        $cache = new PhpFilesAdapter('doctrine_results', 3600, DATABASE_CACHE_PATH . '/cache/');

        // Doctrine ORM 3.x uses attributes instead of annotations
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [MODEL_PATH],
            DEBUG_MODE,
            DATABASE_CACHE_PATH . '/proxy/',
            $cache,
        );

        // In Doctrine ORM 3.x, proxy auto-generation is controlled differently
        // 0 = never, 1 = always, 2 = if file not exists, 3 = if file not exists or changed
        $config->setAutoGenerateProxyClasses(3);

        $this->entityManager = new EntityManager(
            \Doctrine\DBAL\DriverManager::getConnection($dbParams, $config),
            $config,
        );

        $this->dependencyFactory = DependencyFactory::fromEntityManager(
            new JsonFile(BASE_PATH . '/migrations.json'),
            new ExistingEntityManager($this->entityManager),
        );

        $this->testDatabaseConnection();
    }

    public function getEntityManager() : EntityManager
    {
        return $this->entityManager;
    }

    public function getDependencyFactory() : DependencyFactory
    {
        return $this->dependencyFactory;
    }

    private function testDatabaseConnection() : void
    {
        try {
            // In Doctrine DBAL 4.x, use getNativeConnection() or executeQuery() to test the connection
            $this->dependencyFactory->getEntityManager()->getConnection()->getNativeConnection();
        } catch (Exception $exception) {
            http_response_code(500);
            echo '<h2>Error establishing a database connection</h2>';

            if (DEBUG_MODE) {
                echo sprintf('<pre>%s</pre>', $exception->getMessage());
            }

            exit;
        }
    }
}
