<?php

declare(strict_types=1);

use App\Controller\Account;
use App\Controller\Authentication;
use App\Controller\Category;
use App\Controller\Entry;
use App\Controller\Error;
use App\Controller\Media;
use App\Controller\Template;
use App\Controller\User;
use App\Controller\Welcome;
use DI\Container;

return function (Container $container) {
    $container->set(Account::class, \DI\autowire());
    $container->set(Authentication::class, \DI\autowire());
    $container->set(Category::class, \DI\autowire());
    $container->set(Entry::class, \DI\autowire());
    $container->set(Error::class, \DI\autowire());
    $container->set(Media::class, \DI\autowire());
    $container->set(Template::class, \DI\autowire());
    $container->set(User::class, \DI\autowire());
    $container->set(Welcome::class, \DI\autowire());
};
