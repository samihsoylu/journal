<?php

declare(strict_types=1);

namespace App\Framework\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Exception;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

final readonly class DoctrineOrmFactory
{
    private const DEFAULT_CACHE_LIFETIME = 3600;

    public function __construct(
        private string $dbHost,
        private string $dbUsername,
        private string $dbPassword,
        private string $dbSchema,
        private string $rootDirPath,
        private string $databaseCacheDirPath,
        private string $databaseProxyDirPath,
        private string $modelPath,
        private bool $isDebugMode,
    ) {}

    public function createOrm() : DependencyFactory
    {
        $dbParams = [
            'driver' => 'pdo_mysql',
            'host' => $this->dbHost,
            'user' => $this->dbUsername,
            'password' => $this->dbPassword,
            'dbname' => $this->dbSchema,
        ];

        // Doctrine ORM 3.x uses PSR-6 cache directly
        $cache = new PhpFilesAdapter('doctrine_results', self::DEFAULT_CACHE_LIFETIME, $this->databaseCacheDirPath . '/cache/');

        // Doctrine ORM 3.x uses attributes instead of annotations
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [$this->modelPath],
            $this->isDebugMode,
            $this->databaseProxyDirPath . '/proxy/',
            $cache,
        );

        // Auto-generate proxies: 0 = never, 1 = always, 2 = if not exists, 3 = if not exists or changed
        $config->setAutoGenerateProxyClasses(3);

        $entityManager = new EntityManager(
            DriverManager::getConnection($dbParams, $config),
            $config,
        );

        $dependencyFactory = DependencyFactory::fromEntityManager(
            new JsonFile($this->rootDirPath . '/migrations.json'),
            new ExistingEntityManager($entityManager),
        );

        $this->testDatabaseConnection($dependencyFactory);

        return $dependencyFactory;
    }

    private function testDatabaseConnection(DependencyFactory $dependencyFactory) : void
    {
        try {
            // In Doctrine DBAL 4.x, use getNativeConnection() to test the connection
            $dependencyFactory->getEntityManager()->getConnection()->getNativeConnection();
        } catch (Exception $exception) {
            http_response_code(500);
            echo '<h2>Error establishing a database connection</h2>';

            if ($this->isDebugMode) {
                echo sprintf('<pre>%s</pre>', $exception->getMessage());
            }

            exit;
        }
    }
}
