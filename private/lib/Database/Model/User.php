<?php declare(strict_types=1);

namespace App\Database\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * This model class represents a single database record from the `users` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
final class User extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected string $username;

    /**
     * @ORM\Column(type="text")
     */
    protected string $password;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer")
     */
    protected int $lastUpdatedTimestamp;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
