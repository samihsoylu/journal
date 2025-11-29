<?php

declare(strict_types=1);

use App\Database\Database;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;

return function (Container $container) {
    // EntityManager singleton - shared across all repositories
    $container->set(EntityManagerInterface::class, function () {
        $database = new Database();
        return $database->getEntityManager();
    });
};
