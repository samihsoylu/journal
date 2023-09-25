<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework;

use Psr\Container\ContainerInterface;
use RuntimeException;
use SamihSoylu\Journal\Framework\Container\ContainerFactory;

final readonly class Kernel
{
    public ContainerInterface $container;
    public Environment $environment;
    public bool $isDebugMode;

    private function __construct()
    {
        $this->assertEnvironmentVariablesAreSet();

        $this->environment = Environment::from($_ENV['JOURNAL_ENV']);
        $this->isDebugMode = $_ENV['JOURNAL_ENABLE_DEBUG'];

        $this->initializeContainer();
    }

    public static function boot(): self
    {
        return new self();
    }

    private function initializeContainer(): void
    {
        $factory = new ContainerFactory(
            $_ENV['JOURNAL_CONFIG_DIR'],
            $this->environment,
        );

        $this->container = $factory->create();
    }

    private function assertEnvironmentVariablesAreSet(): void
    {
        $requiredFields = ['JOURNAL_ENV', 'JOURNAL_ENABLE_DEBUG', 'JOURNAL_CONFIG_DIR'];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $_ENV)) {
                throw new RuntimeException("Environment variable \$_ENV['{$field}'] not found");
            }
        }
    }
}