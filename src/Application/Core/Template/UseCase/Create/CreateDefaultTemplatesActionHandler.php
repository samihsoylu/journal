<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\Template\UseCase\Create;

use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\SecureCacheable;
use SamihSoylu\Utility\Assert;

/**
 * @implements ActionHandlerInterface<CreateDefaultTemplatesAction>
 */
final readonly class CreateDefaultTemplatesActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TemplateRepositoryInterface $templateRepository,
        private PasswordKeyManagerInterface $passwordKeyManager,
        private SecureCacheable $secureCache,
    ) {}

    public function __invoke(ActionInterface $action): void
    {
        $user = $this->userRepository->getById($action->userId);
        Assert::notNull($user, "User[id={$action->userId}] not found");

        $userPassword = $this->secureCache->get($action->passwordTransientId);

        $protectedKey = $user->getProtectedKey();
        $encryptionKey = $this->passwordKeyManager->unlockProtectedKey($user->getProtectedKey(), $userPassword);

        $this->passwordKeyManager->encryptData('', $encryptionKey);
        $template = new Template();
        // @todo build this up

        $this->templateRepository
            ->queueForSaving($template)
            ->saveChanges();

        $this->secureCache->remove($action->passwordTransientId);
    }
}