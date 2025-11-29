<?php

declare(strict_types=1);

namespace App\Service\Model;

final readonly class EntryDecorator
{
    public function __construct(
        private int $id,
        private string $title,
        private int $categoryId,
        private string $categoryName,
        private string $content,
        private string $getLastUpdatedTimestamp,
    ) {}

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getCategoryId() : int
    {
        return $this->categoryId;
    }

    public function getCategoryName() : string
    {
        return $this->categoryName;
    }

    public function getContent() : string
    {
        return $this->content;
    }

    public function getLastUpdatedTimestamp() : string
    {
        return $this->getLastUpdatedTimestamp;
    }
}
