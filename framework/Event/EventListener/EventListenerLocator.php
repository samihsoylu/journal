<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event\EventListener;

use Symfony\Component\Finder\Finder;

final class EventListenerLocator
{
    public function __construct(
        private string $sourceDir,
    ) {}

    public function findEventListenerFiles(): array
    {
        $finder = new Finder();
        $files = $finder->files()->in($this->sourceDir)->name('*EventListener.php');
        return $files->hasResults() ? iterator_to_array($files) : [];
    }
}