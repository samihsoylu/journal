<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event\EventSubscriber;

use Symfony\Component\Finder\Finder;

final class EventSubscriberLocator
{
    public function __construct(
        private string $sourceDir,
    ) {}

    public function findEventSubscriberFiles(): array
    {
        $finder = new Finder();
        $files = $finder->files()->in($this->sourceDir)->name('*EventSubscriber.php');
        return $files->hasResults() ? iterator_to_array($files) : [];
    }
}