<?php declare(strict_types=1);

namespace App\Database\Model;

use App\Utility\Encryptor;
use App\Utility\UserSession;
use Defuse\Crypto\Key;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping as ORM;

/**
 * This model class represents a single database record from the `entries` table.
 *
 * @ORM\Entity
 * @ORM\Table(name="entries",indexes={@Index(name="search_by_userid_categoryid_createdtimestamp", columns={"userId", "categoryId", "createdTimestamp"})})
 */
class Entry extends AbstractModel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /**
     * @ManyToOne(targetEntity="Category")
     * @JoinColumn(name="categoryId", referencedColumnName="id", nullable=false)
     */
    protected Category $referencedCategory;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="userId", referencedColumnName="id", nullable=false)
     */
    protected User $referencedUser;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $title;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $content;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $createdTimestamp;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $lastUpdatedTimestamp;

    public function getReferencedCategory(): Category
    {
        return $this->referencedCategory;
    }

    public function setReferencedCategory(Category $referencedCategory): self
    {
        $this->referencedCategory = $referencedCategory;

        return $this;
    }

    public function getReferencedUser(): User
    {
        return $this->referencedUser;
    }

    public function setReferencedUser(User $referencedUser): self
    {
        $this->referencedUser = $referencedUser;

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

    public function getContentAsMarkup(Key $encryptionKey): string
    {
        $parser = new \Parsedown();
        $parser->setSafeMode(true);

        return $parser->text($this->getContentDecrypted($encryptionKey));
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContentDecrypted(Key $encryptionKey): string
    {
        $encryptor = new Encryptor();
        return $encryptor->decrypt($this->content, $encryptionKey);
    }

    public function setContentAndEncrypt(string $content, Key $encryptionKey): self
    {
        $encryptor = new Encryptor();
        $encryptedContent = $encryptor->encrypt($content, $encryptionKey);

        $this->setContent($encryptedContent);

        return $this;
    }
}
