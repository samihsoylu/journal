<?php

declare(strict_types=1);

namespace App\Framework\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

final readonly class DoctrineOrmFactory
{
    private const DEFAULT_CACHE_LIFETIME = 3600;
    private const CACHE_NAMESPACE = 'doctrine_php85';

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
        $cache = new PhpFilesAdapter(self::CACHE_NAMESPACE, self::DEFAULT_CACHE_LIFETIME, $this->databaseCacheDirPath . '/cache/');

        // Doctrine ORM 3.x uses attributes instead of annotations
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [$this->modelPath],
            $this->isDebugMode,
            $this->databaseProxyDirPath . '/proxy/',
            $cache,
        );

        // PHP 8.4+ provides native lazy objects. Doctrine requires this mode
        // when Symfony VarExporter 8 is installed.
        $config->enableNativeLazyObjects(true);

        // Auto-generate proxies: 0 = never, 1 = always, 2 = if not exists, 3 = if not exists or changed
        $config->setAutoGenerateProxyClasses(3);

        $entityManager = new EntityManager(
            DriverManager::getConnection($dbParams, $config),
            $config,
        );

        return DependencyFactory::fromEntityManager(
            new JsonFile($this->rootDirPath . '/migrations.json'),
            new ExistingEntityManager($entityManager),
        );
    }
}
