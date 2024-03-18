<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Routing\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class ValueResolver implements ValueResolverInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return [$this->container->get($argument->getType())];
    }
}