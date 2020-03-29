<?php declare(strict_types=1);

namespace App\Database;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;

final class Database
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * @var EntityManager
     */
    private $entityManager;

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
            $_ENV['DEV_MODE'],
            null,
            null,
            false
        );

        try {
            $this->entityManager = EntityManager::create($dbParams, $config);
        } catch (ORMException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
