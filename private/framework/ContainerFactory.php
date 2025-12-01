<?php

declare(strict_types=1);

namespace App\Framework;

use DI\Container;
use DI\ContainerBuilder;

final class ContainerFactory
{
    private string $configPath;
    private string $environment;

    public function __construct(string $configPath, string $environment)
    {
        $this->configPath = $configPath;
        $this->environment = $environment;
    }

    public function create() : Container
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        $container = $containerBuilder->build();

        $this->loadConfigs($container, $this->environment);

        return $container;
    }

    private function loadConfigs(Container $container, string $env) : void
    {
        // Always load prod configs first as the base
        $this->loadConfigsFromPath($container, "{$this->configPath}/services/prod");

        // Then load environment-specific configs (may override prod)
        if ($env !== 'prod') {
            $this->loadConfigsFromPath($container, "{$this->configPath}/services/{$env}");
        }
    }

    private function loadConfigsFromPath(Container $container, string $servicesPath) : void
    {
        if ( ! is_dir($servicesPath)) {
            return;
        }

        $files = glob("{$servicesPath}/*.php") ?: [];

        foreach ($files as $file) {
            $configurator = require $file;

            if (is_callable($configurator)) {
                $configurator($container);
            }
        }
    }
}
