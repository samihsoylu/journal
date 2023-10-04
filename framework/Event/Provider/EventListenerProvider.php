<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Event\Provider;

use Symfony\Component\Finder\Finder;

final readonly class EventListenerProvider
{
    public function __construct(
        private Finder $finder,
        private string $sourceDir,
    ) {}

    public function findEventListenerFiles(): array
    {
        $files = $this->finder->files()->in($this->sourceDir)->name('*Listener.php');

        return $files->hasResults() ? iterator_to_array($files) : [];
    }
}
