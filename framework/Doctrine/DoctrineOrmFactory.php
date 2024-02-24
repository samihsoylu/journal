<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Doctrine;

use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Proxy\ProxyFactory;
use Linkin\Component\DoctrineNamingStrategy\ORM\Mapping\CamelCaseNamingStrategy;
use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

final readonly class DoctrineOrmFactory
{
    private const DEFAULT_CACHE_LIFETIME = 3600;

    public function __construct(
        private string $databaseDsn,
        private string $rootDirPath,
        private string $databaseCacheDirPath,
        private string $databaseEntityDirPath,
        private string $databaseProxyDirPath,
        private bool $isDebugMode,
    ) {}

    public function create(): DependencyFactory
    {
        $adapter = new PhpFilesAdapter('cache', self::DEFAULT_CACHE_LIFETIME, $this->databaseCacheDirPath);
        $provider = DoctrineProvider::wrap($adapter);
        $cache = CacheAdapter::wrap($provider);

        $config = ORMSetup::createAttributeMetadataConfiguration(
            [$this->databaseEntityDirPath],
            $this->isDebugMode,
            $this->databaseProxyDirPath,
            $cache
        );
        $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED);
        $config->setNamingStrategy(new CamelCaseNamingStrategy());

        $dsnParser = new DsnParser($this->getSchemeMapping());
        $dbConfig = $dsnParser->parse($this->databaseDsn);

        $this->setCustomTypes();

        $entityManager = new EntityManager(
            DriverManager::getConnection(
                $dbConfig,
                $config
            ),
            $config
        );

        return DependencyFactory::fromEntityManager(
            new JsonFile("{$this->rootDirPath}/migrations.json"),
            new ExistingEntityManager($entityManager)
        );
    }

    private function setCustomTypes(): void
    {
        Type::addType(UuidType::NAME, UuidType::class);
    }

    /**
     * @return array<string>
     */
    private function getSchemeMapping(): array
    {
        return [
            'db2' => 'ibm_db2',
            'mssql' => 'pdo_sqlsrv',
            'mysql' => 'pdo_mysql',
            'postgres' => 'pdo_pgsql',
            'postgresql' => 'pdo_pgsql',
            'pgsql' => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'sqlite3' => 'pdo_sqlite',
        ];
    }
}
