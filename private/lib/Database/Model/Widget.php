<?php

declare(strict_types=1);

namespace App\Database\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Override;

/**
 * This model class represents a single database record from the `widgets` table.
 */
#[ORM\Entity]
#[ORM\Table(name: 'widgets')]
#[ORM\HasLifecycleCallbacks]
#[UniqueConstraint(name: 'unique_widget_name', columns: ['userId', 'name'])]
class Widget extends AbstractModel
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[Override]
    protected int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'userId', referencedColumnName: 'id', nullable: false)]
    protected User $referencedUser;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $enabled;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Override]
    protected int $createdTimestamp;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Override]
    protected int $lastUpdatedTimestamp;

    public function getReferencedUser() : User
    {
        return $this->referencedUser;
    }

    public function setReferencedUser(User $user) : void
    {
        $this->referencedUser = $user;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) : void
    {
        $this->enabled = $enabled;
    }
}
