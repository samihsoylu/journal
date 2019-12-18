<?php

namespace App\Database\Model;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping as ORM;

/**
 * This model class represents a single database record from the `notes` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="notes")
 */
class Note extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Category")
     * @JoinColumn(name="categoryId", referencedColumnName="id")
     */
    protected $categoryId;

    /**
     * @ORM\Column(type="text")
     */
    protected $context;

    /**
     * @ORM\Column(type="integer")
     */
    protected $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $lastUpdatedTimestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
    }

    public function setCreatedTimestamp($createdTimestamp): self
    {
        $this->createdTimestamp = $createdTimestamp;
        return $this;
    }

    public function getLastUpdatedTimestamp(): int
    {
        return $this->lastUpdatedTimestamp;
    }

    public function setLastUpdatedTimestamp($lastUpdatedTimestamp): self
    {
        $this->lastUpdatedTimestamp = $lastUpdatedTimestamp;
        return $this;
    }
}