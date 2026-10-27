<?php

declare(strict_types=1);

namespace App\Framework;

use DI\Container;

final class Kernel
{
    private Container $container;

    public function __construct(string $environment, bool $debug)
    {
        $this->container = new ContainerFactory(BASE_PATH . '/config', $environment)->create();
        $this->container->set('kernel.environment', $environment);
        $this->container->set('kernel.debug', $debug);
    }

    public function get(string $id) : mixed
    {
        return $this->container->get($id);
    }

    public function getContainer() : Container
    {
        return $this->container;
    }
}
