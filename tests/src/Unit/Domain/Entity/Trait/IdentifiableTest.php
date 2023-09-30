<?php

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use Ramsey\Uuid\Uuid;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeEntity;

it('should return null when the ID is not set', function () {
    $entity = new FakeEntity();

    expect($entity->getId())->toBeNull();
});

it('should return the correct UUID when the ID is set', function () {
    $entity = new FakeEntity();

    $uuid = Uuid::uuid4();
    $entity->setId($uuid);

    expect($entity->getId())->toBeInstanceOf(UuidInterface::class)
        ->and($entity->getId()->toString())->toBe($uuid->toString());
});