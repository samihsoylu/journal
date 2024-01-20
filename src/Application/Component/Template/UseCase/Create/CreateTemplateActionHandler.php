<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\Template\UseCase\Create;

use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Application\Component\Template\Event\TemplateCreatedEvent;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;
use SamihSoylu\Utility\Assert;

/**
 * @implements ActionHandlerInterface<CreateTemplateAction>
 */
final readonly class CreateTemplateActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private TemplateRepositoryInterface $templateRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ActionInterface $action): void
    {
        $user = $this->getUserById($action->userId);
        $category = $this->getCategoryById($action->categoryId);
        $category->assertBelongsToUser($user);

        $template = new Template();
        $template->setTitle($action->title)
            ->setContent($action->content)
            ->setUser($user)
            ->setCategory($category);

        $this->templateRepository->queueForSaving($template)
            ->saveChanges();

        $this->eventDispatcher->dispatch(new TemplateCreatedEvent(
            $user->getId()->toString(),
            $template->getId()->toString(),
        ));
    }

    private function getUserById(string $userId): User
    {
        $user = $this->userRepository->getById($userId);
        Assert::notNull($user, "User[id={$userId}] not found");

        return $user;
    }

    private function getCategoryById(string $categoryId): Category
    {
        $category = $this->categoryRepository->getById($categoryId);
        Assert::notNull($category, "Category[id={$categoryId}] not found");

        return $category;
    }
}
