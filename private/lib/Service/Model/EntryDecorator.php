<?php declare(strict_types=1);

namespace App\Service\Model;

class EntryDecorator
{
    private int $id;
    private string $title;
    private int $categoryId;
    private string $categoryName;
    private string $content;
    private string $getLastUpdatedTimestamp;

    public function __construct(
        int $id,
        string $title,
        int $categoryId,
        string $categoryName,
        string $content,
        string $getLastUpdatedTimestamp
    ) {
        $this->id                      = $id;
        $this->title                   = $title;
        $this->categoryId              = $categoryId;
        $this->categoryName            = $categoryName;
        $this->content                 = $content;
        $this->getLastUpdatedTimestamp = $getLastUpdatedTimestamp;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
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

    public function getLastUpdatedTimestamp(): string
    {
        return $this->getLastUpdatedTimestamp;
    }
}
