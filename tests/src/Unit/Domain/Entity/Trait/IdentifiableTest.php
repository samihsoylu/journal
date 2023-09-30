<?php

use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use Ramsey\Uuid\Uuid;

it('should correctly set and get uuid', function () {
    // Create an entity class that uses Identifiable trait for testing
    $entity = new class {
        use Identifiable;
    };

    $uuid = Uuid::uuid4();

    $entity->setId($uuid);

    expect($entity->getId())->toBe($uuid);
});

it('should return null for unset uuid', function () {
    // Create an entity class that uses Identifiable trait for testing
    $entity = new class {
        use Identifiable;
    };

    expect($entity->getId())->toBeNull();
});

it('should throw type error when setting non-uuid value', function () {
    // Create an entity class that uses Identifiable trait for testing
    $entity = new class {
        use Identifiable;
    };

    expect(fn() => $entity->setId('not-a-uuid'))->toThrow(\TypeError::class);
});
