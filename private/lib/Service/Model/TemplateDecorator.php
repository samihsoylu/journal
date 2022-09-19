<?php declare(strict_types=1);

namespace App\Service\Model;

class TemplateDecorator implements \JsonSerializable
{
    private int $id;
    private string $title;
    private int $categoryId;
    private string $categoryName;
    private string $content;

    public function __construct(
        int $id,
        string $title,
        int $categoryId,
        string $categoryName,
        string $content
    ) {
        $this->id           = $id;
        $this->title        = $title;
        $this->categoryId   = $categoryId;
        $this->categoryName = $categoryName;
        $this->content      = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
