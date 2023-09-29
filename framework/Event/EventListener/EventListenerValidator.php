<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event\EventListener;

use ReflectionClass;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\MethodMissingException;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\NotFoundException;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\ParameterMissingException;

final class EventListenerValidator
{
    public function validateListener(string $fqcn): void
    {
        // Assert::classExists($fqcn);
        $this->assertClassExists($fqcn, "Class '{$fqcn}' not found");

        $listener = new ReflectionClass($fqcn);
        $this->assertListenerHasInvokeMethod($listener, $fqcn);
        $this->assertInvokeMethodHasParameter($listener, $fqcn);
    }

    private function assertClassExists(string $fqcn, string $message): void
    {
        if (!class_exists($fqcn)) {
            throw new NotFoundException($message);
        }
    }

    private function assertListenerHasInvokeMethod(ReflectionClass $listener, string $fqcn): void
    {
        if (!$listener->hasMethod('__invoke')) {
            throw new MethodMissingException(
                "Listener class '{$fqcn}' is missing a required __invoke(Event \$event) method. Ensure that the class has an __invoke method with a single parameter of type Event."
            );
        }
    }

    private function assertInvokeMethodHasParameter(ReflectionClass $listener, string $fqcn): void
    {
        $parameters = $listener->getMethod('__invoke')->getParameters();
        $parameter = $parameters[0] ?? false;

        if (!$parameter) {
            throw new ParameterMissingException(
                "The __invoke() method in the listener class '{$fqcn}' is missing its required first parameter. This parameter should be an object. Make sure the method signature is __invoke(Event \$event)."
            );
        }
    }
}