<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Container;

use DI\Container;
use LogicException;
use SamihSoylu\Journal\Framework\Environment;
use Symfony\Component\Finder\Finder;

final readonly class ContainerFactory
{
    public function __construct(
        private string  $configDir,
        private Environment $environment,
    ) {}

    public function create(): Container
    {
        $container = new Container();

        $this->configureContainer($container);

        return $container;
    }

    private function configureContainer(Container $container): void
    {
        $environments = array_unique([
            Environment::PROD->value,
            $this->environment->value,
        ]);

        $find = new Finder();
        foreach ($environments as $environment) {
            $directoryPath = "{$this->configDir}/services/{$environment}/";

            foreach ($find->files()->in($directoryPath) as $file) {
                $configurator = require $file;
                if (!is_callable($configurator)) {
                    throw new LogicException("Expected '{$file}' to return a callable configurator.");
                }

                $configurator($container);
            }
        }
    }
}
