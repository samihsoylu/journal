<?php

declare(strict_types=1);

use SamihSoylu\Journal\Domain\Entity\Trait\Timestampable;

it('should correctly set the created at timestamp on prepersist', function (): void {
    $entity = new class () {
        use Timestampable;

        public function simulatePrePersist(): void
        {
            $this->onPrePersist();
        }
    };

    $entity->simulatePrePersist();

    expect($entity->getCreatedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('should correctly set the updatedat timestamp on preupdate', function (): void {
    $entity = new class () {
        use Timestampable;

        public function simulatePreUpdate(): void
        {
            $this->onPreUpdate();
        }
    };

    $entity->simulatePreUpdate();

    expect($entity->getUpdatedAt())->toBeInstanceOf(DateTimeImmutable::class);
});

it('should return null for unset created at timestamp', function (): void {
    $entity = new class () {
        use Timestampable;
    };

    expect($entity->getCreatedAt())->toBeNull();
});

it('should return null for unset updated at timestamp', function (): void {
    $entity = new class () {
        use Timestampable;
    };

    expect($entity->getUpdatedAt())->toBeNull();
});
