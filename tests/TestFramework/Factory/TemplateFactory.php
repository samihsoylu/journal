<?php

declare(strict_types=1);

namespace Tests\TestFramework\Factory;

use App\Database\Model\Category;
use App\Database\Model\Template;
use App\Database\Model\User;
use App\Utility\Encryptor;
use Defuse\Crypto\Key;
use Tests\TestFramework\TestContext;

/**
 * Factory for creating Template entities in tests.
 *
 * Usage:
 *   $template = TemplateFactory::setup()
 *       ->withTitle('My Template')
 *       ->withContent('Template content', $encryptionKey)
 *       ->withUser($user)
 *       ->withCategory($category)
 *       ->create();
 *
 * If no user/category is provided, they will be auto-created.
 * Content is encrypted using the entity's setContentAndEncrypt method.
 */
final class TemplateFactory
{
    private ?User $user = null;
    private ?Category $category = null;
    private string $title = 'Test Template';
    private string $content = 'Test template content';
    private ?Key $encryptionKey = null;

    private function __construct() {}

    public static function setup() : self
    {
        return new self();
    }

    public function withUser(User $user) : self
    {
        $clone = clone $this;
        $clone->user = $user;

        return $clone;
    }

    public function withCategory(Category $category) : self
    {
        $clone = clone $this;
        $clone->category = $category;

        return $clone;
    }

    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    public function withContent(string $content, Key $encryptionKey) : self
    {
        $clone = clone $this;
        $clone->content = $content;
        $clone->encryptionKey = $encryptionKey;

        return $clone;
    }

    public function create() : Template
    {
        // Auto-create user if not provided
        $user = $this->user;
        $encryptionKey = $this->encryptionKey;

        if ( ! $user instanceof User) {
            $userFactory = UserFactory::setup();
            $user = $userFactory->create();
            $encryptionKey ??= $userFactory->getDecryptionKey();
        }

        // Auto-create category if not provided
        $category = $this->category ?? CategoryFactory::setup()
            ->withUser($user)
            ->create()
        ;

        // Generate encryption key if still not set
        if ( ! $encryptionKey instanceof Key) {
            $encryptor = new Encryptor();
            $encryptionKey = $encryptor->getKeyFromProtectedKey($user->getEncryptionKey(), 'testpassword');
        }

        $template = new Template();
        $template->setReferencedUser($user)
            ->setReferencedCategory($category)
            ->setTitle($this->title)
            ->setContentAndEncrypt($this->content, $encryptionKey)
        ;

        TestContext::$testOrm?->save($template);

        return $template;
    }
}
