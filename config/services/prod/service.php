<?php

declare(strict_types=1);

use App\Service\AuthenticationService;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Service\MediaService;
use App\Service\TemplateService;
use App\Service\UserService;
use App\Service\WidgetService;
use DI\Container;

return function (Container $container) {
    $container->set(AuthenticationService::class, \DI\autowire());
    $container->set(UserService::class, \DI\autowire());
    $container->set(EntryService::class, \DI\autowire());
    $container->set(CategoryService::class, \DI\autowire());
    $container->set(TemplateService::class, \DI\autowire());
    $container->set(WidgetService::class, \DI\autowire());
    $container->set(MediaService::class, \DI\autowire());
};
