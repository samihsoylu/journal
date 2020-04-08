<?php declare(strict_types=1);

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
final class Note extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ManyToOne(targetEntity="Category")
     * @JoinColumn(name="categoryId", referencedColumnName="id")
     */
    protected int $categoryId;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="userId", referencedColumnName="id")
     */
    protected int $userId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected string $title;

    /**
     * @ORM\Column(type="text")
     */
    protected string $content;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $lastUpdatedTimestamp;

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
