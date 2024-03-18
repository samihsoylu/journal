<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Routing\Controller;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver AS SymfonyControllerResolver;

final class ControllerResolver extends SymfonyControllerResolver
{
    public function __construct(
        private ContainerInterface $container,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function instantiateController(string $class): object
    {
        return $this->container->get($class);
    }
}