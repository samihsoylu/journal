<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Event\EventListener\EventListenerValidator;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\MethodMissingException;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\NotFoundException;
use SamihSoylu\Journal\Framework\Event\EventListener\Exception\ParameterMissingException;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\EventListenerWithNoInvokeMethod;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\EventListenerWithNoParamInInvokeMethod;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\EventListenerWithValidInvoke;

it('should throw exception when class does not exist', function () {
    $validator = new EventListenerValidator();
    $className = 'NonExistentClass';

    $validator->validateListener($className);
})->throws(NotFoundException::class);

it('should throw exception when __invoke method is missing', function () {
    $validator = new EventListenerValidator();
    $className = EventListenerWithNoInvokeMethod::class;

    $validator->validateListener($className);
})->throws(MethodMissingException::class);

it('should throw exception when __invoke method has no parameters', function () {
    $validator = new EventListenerValidator();
    $className = EventListenerWithNoParamInInvokeMethod::class;

    $validator->validateListener($className);
})->throws(ParameterMissingException::class);

it('should not throw any exceptions for a valid listener', function () {
    $validator = new EventListenerValidator();
    $className = EventListenerWithValidInvoke::class;

    $validator->validateListener($className);
})->throwsNoExceptions();