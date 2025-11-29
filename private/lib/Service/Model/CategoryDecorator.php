<?php

declare(strict_types=1);

namespace App\Service\Model;

final readonly class CategoryDecorator
{
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private int $totalEntries,
        private int $totalTemplates,
    ) {}

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function getTotalEntries() : int
    {
        return $this->totalEntries;
    }

    public function getTotalTemplates() : int
    {
        return $this->totalTemplates;
    }
}
