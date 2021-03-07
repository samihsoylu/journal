<?php declare(strict_types=1);

namespace App\Database;

use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

final class Database
{
    private static ?Database $instance = null;

    private DependencyFactory $dependencyFactory;

    private function __construct()
    {
        $dbParams = [
            'driver'   => 'pdo_mysql',
            'host'     => $_ENV['DB_HOST'],
            'user'     => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'dbname'   => $_ENV['DB_SCHEMA'],
        ];

        $config = Setup::createAnnotationMetadataConfiguration(
            [MODEL_PATH],
            DEBUG_MODE,
            DATABASE_CACHE_PATH . '/proxy/',
            new PhpFileCache(DATABASE_CACHE_PATH . '/cache/'),
            false
        );

        $this->entityManager = EntityManager::create($dbParams, $config);

        $this->dependencyFactory = DependencyFactory::fromEntityManager(new JsonFile(BASE_PATH . '/migrations.json'), new ExistingEntityManager($this->entityManager));
    }

    public static function getInstance(): DependencyFactory
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return (self::$instance)->dependencyFactory;
    }
}
