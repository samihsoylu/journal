<?php

declare(strict_types=1);

test('all entity classes are not marked as final')
    ->expect('SamihSoylu\Journal\Domain\Entity')
    ->classes()
    ->not()
    ->toBeFinal();

test('all entity traits are used in entities only')
    ->expect('SamihSoylu\Journal\Domain\Entity\Trait')
    ->toOnlyBeUsedIn('SamihSoylu\Journal\Domain\Entity');

test('all entity traits are traits')
    ->expect('SamihSoylu\Journal\Domain\Entity\Trait')
    ->toBeTraits();

test('all repository classes are marked as final')
    ->expect('SamihSoylu\Journal\Domain\Repository')
    ->classes()
    ->toBeFinal();

test('all doctrine repository classes inherit Doctrine\ORM\EntityRepository')
    ->expect('SamihSoylu\Journal\Domain\Repository\Doctrine')
    ->classes()
    ->toExtend(\Doctrine\ORM\EntityRepository::class);
