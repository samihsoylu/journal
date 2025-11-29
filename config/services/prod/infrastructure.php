<?php

declare(strict_types=1);

use App\Database\Database;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;

return static function (Container $container) : void {
    // EntityManager singleton - shared across all repositories
    $container->set(EntityManagerInterface::class, static function () {
        $database = new Database();

        return $database->getEntityManager();
    });
};
