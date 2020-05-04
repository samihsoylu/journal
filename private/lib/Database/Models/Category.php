<?php declare(strict_types=1);

namespace App\Database\Models;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * This model class represents a single database record from the `categories` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
final class Category extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="userId", referencedColumnName="id")
     */
    protected int $userId;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected string $categoryName;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $lastUpdatedTimestamp;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }
}
