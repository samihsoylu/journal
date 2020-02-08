<?php

namespace App\Database\Model;

use Doctrine\ORM\Mapping as ORM;

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
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $categoryName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $lastUpdatedTimestamp;

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