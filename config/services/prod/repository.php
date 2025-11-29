<?php

declare(strict_types=1);

use App\Database\Repository\CategoryRepository;
use App\Database\Repository\EntryRepository;
use App\Database\Repository\TemplateRepository;
use App\Database\Repository\UserRepository;
use App\Database\Repository\WidgetRepository;
use DI\Container;

return function (Container $container) {
    $container->set(UserRepository::class, \DI\autowire());
    $container->set(EntryRepository::class, \DI\autowire());
    $container->set(CategoryRepository::class, \DI\autowire());
    $container->set(TemplateRepository::class, \DI\autowire());
    $container->set(WidgetRepository::class, \DI\autowire());
};
