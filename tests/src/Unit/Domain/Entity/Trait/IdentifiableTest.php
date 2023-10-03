<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEntity;

it('should return null when the ID is not set', function (): void {
    $entity = new FakeEntity();

    expect($entity->getId())->toBeNull();
});

it('should return the correct UUID when the ID is set', function (): void {
    $entity = new FakeEntity();

    $uuid = Uuid::uuid4();
    $entity->setId($uuid);

    expect($entity->getId())->toBeInstanceOf(UuidInterface::class)
        ->and($entity->getId()->toString())->toBe($uuid->toString());
});
