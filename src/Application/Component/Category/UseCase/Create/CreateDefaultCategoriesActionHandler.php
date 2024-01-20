<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\Category\UseCase\Create;

use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Application\Component\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;
use SamihSoylu\Utility\Assert;

/**
 * @implements ActionHandlerInterface<CreateDefaultCategoriesAction>
 */
final class CreateDefaultCategoriesActionHandler implements ActionHandlerInterface
{
    private int $categoryCount = 0;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ActionInterface $action): void
    {
        $user = $this->getUser($action->userId);

        $categories = $this->getDefaultCategories();
        foreach ($categories as $category) {
            $category->setUser($user);
            $this->categoryRepository->queueForSaving($category);
        }
        $this->categoryRepository->saveChanges();

        $this->dispatchDefaultCategoriesCreatedEvent($action);
    }

    /**
     * @return array<Category>
     */
    private function getDefaultCategories(): array
    {
        $categories = [
            ['name' => 'Personal', 'description' => 'Stories about your experiences, passions and ambitions'],
            ['name' => 'Food', 'description' => 'Food journaling for reaching healthy eating goals'],
            ['name' => 'Work', 'description' => 'Meeting notes, deadlines, countless other bits of information that are best stored here instead of your brain'],
        ];

        $mappedCategories = [];
        foreach ($categories as $categoryItem) {
            $category = new Category();
            $category->setName($categoryItem['name'])
                ->setDescription($categoryItem['description'])
                ->setPosition(++$this->categoryCount);

            $mappedCategories[] = $category;
        }

        return $mappedCategories;
    }

    protected function dispatchDefaultCategoriesCreatedEvent(CreateDefaultCategoriesAction $action): void
    {
        $this->eventDispatcher->dispatch(
            new DefaultCategoriesCreatedEvent(
                $action->userId,
                $action->passwordTransientId,
            )
        );
    }

    protected function getUser(string $userId): User
    {
        $user = $this->userRepository->getById($userId);
        Assert::notNull($user, "User[id={$userId}] not found");

        return $user;
    }
}
