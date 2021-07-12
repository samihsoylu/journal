<?php declare(strict_types=1);

namespace App\Database\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * This model class represents a single database record from the `categories` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="categories", uniqueConstraints={
 *     @UniqueConstraint(name="unique_category_name",columns={"userId", "name"})
 * })
 */
class Category extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="userId", referencedColumnName="id", nullable=false)
     */
    protected User $referencedUser;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $description;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $sortOrder;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $lastUpdatedTimestamp;

    public function getReferencedUser(): User
    {
        return $this->referencedUser;
    }

    public function setReferencedUser(User $user): self
    {
        $this->referencedUser = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
