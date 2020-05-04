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
    public const PRIVILEGE_LEVEL_USER = 1;
    public const PRIVILEGE_LEVEL_ADMIN = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $username;

    /**
     * @ORM\Column(type="text")
     */
    protected string $password;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected string $emailAddress;

    /**
     * @ORM\Column(type="integer", options={"unsigned":true, "default":0});
     */
    protected int $privilegeLevel;

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

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrivilegeLevel(): int
    {
        return $this->privilegeLevel;
    }

    /**
     * @param int $privilegeLevel
     */
    public function setPrivilegeLevel(int $privilegeLevel): void
    {
        $this->privilegeLevel = $privilegeLevel;
    }
}
