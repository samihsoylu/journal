<?php

use DI\Container;
use SamihSoylu\Journal\Application\Service\Contract\UserServiceInterface;
use SamihSoylu\Journal\Application\Service\UserService;

return function (Container $container): void {
    $container->set(UserServiceInterface::class, fn (Container $container): UserServiceInterface => $container->get(UserService::class));
};
