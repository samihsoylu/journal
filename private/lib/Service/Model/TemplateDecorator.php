<?php

declare(strict_types=1);

namespace App\Service\Model;

use JsonSerializable;
use ReturnTypeWillChange;

final readonly class TemplateDecorator implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $title,
        private int $categoryId,
        private string $categoryName,
        private string $content,
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

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
