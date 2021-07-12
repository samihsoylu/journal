<?php declare(strict_types=1);


namespace App\Service\Model;


class CategoryDecorator
{
    private int $id;
    private string $name;
    private string $description;

    private int $totalEntries;
    private int $totalTemplates;

    public function __construct(
        int $id,
        string $name,
        string $description,

        int $totalEntries,
        int $totalTemplates
    ) {
        $this->id             = $id;
        $this->name           = $name;
        $this->description    = $description;

        $this->totalEntries   = $totalEntries;
        $this->totalTemplates = $totalTemplates;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTotalEntries(): int
    {
        return $this->totalEntries;
    }

    public function getTotalTemplates(): int
    {
        return $this->totalTemplates;
    }
}