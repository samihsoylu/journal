<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Listener\Template;

use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Application\Component\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Application\Component\Template\UseCase\Create\CreateTemplateAction;
use SamihSoylu\Journal\Application\Listener\Template\Dto\TemplateDto;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;
use SamihSoylu\Utility\Assert;

final readonly class CreateDefaultUserTemplatesListener
{
    public function __construct(
        private ActionDispatcherInterface $actionDispatcher,
        private CategoryRepositoryInterface $categoryRepository,
        private PasswordKeyManagerInterface $passwordKeyManager,
        private SecureCacheable $secureCache,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(DefaultCategoriesCreatedEvent $event): void
    {
        $user = $this->getUserById($event->userId);
        $encryptionKey = $this->passwordKeyManager->unlockProtectedKey(
            $user->getProtectedKey(),
            $this->getPasswordFromEncryptedCache($event->passwordTransientId)
        );

        $defaultFoodTemplate = $this->getDefaultFoodTemplate();
        $encryptedContent = $this->passwordKeyManager->encryptData($defaultFoodTemplate->content, $encryptionKey);
        $this->actionDispatcher->dispatch(
            new CreateTemplateAction(
                $defaultFoodTemplate->title,
                $encryptedContent,
                $user->getId()->toString(),
                $this->getFoodCategory($event->userId)->getId()->toString(),
            )
        );

        $this->secureCache->remove($event->passwordTransientId);
    }

    public function getDefaultFoodTemplate(): TemplateDto
    {
        return new TemplateDto(
            'Food',
            "# Breakfast\n\n* ...\n* ...\n* ...\n\n# Lunch\n\n* ...\n* ...\n* ...\n\n# Dinner\n\n* ...\n* ...\n* ...\n\n"
        );
    }

    private function getPasswordFromEncryptedCache(string $transientId): string
    {
        $password = $this->secureCache->get($transientId);
        Assert::notNull($password, "CacheItem[transientId={$transientId}] not found");

        return $password;
    }

    private function getUserById(string $userId): User
    {
        $user = $this->userRepository->getById($userId);
        Assert::notNull($user, "User[id={$userId}] not found");

        return $user;
    }

    public function getFoodCategory(string $userId): Category
    {
        $category = $this->categoryRepository->getByName($userId, 'Food');
        Assert::notNull($category, "Category[title=Food] not found for User[id={$userId}]");

        return $category;
    }
}
