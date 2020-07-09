<?php declare(strict_types=1);

namespace App\Database;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

final class Database
{
    private static ?Database $instance = null;

    private EntityManager $entityManager;

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
            $_ENV['DEBUG_MODE'],
            null,
            null,
            false
        );

        $this->entityManager = EntityManager::create($dbParams, $config);
    }

    public static function getInstance(): EntityManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return (self::$instance)->entityManager;
    }
}
