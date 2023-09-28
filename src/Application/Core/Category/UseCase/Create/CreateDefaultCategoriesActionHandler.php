<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\Category\UseCase\Create;

use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\Journal\Application\Core\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Application\Core\Category\UseCase\User;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

/**
 * @implements ActionHandlerInterface<CreateDefaultCategoriesAction>
 */
final class CreateDefaultCategoriesActionHandler implements ActionHandlerInterface
{
    private int $categoryCount = 0;

    private function __construct(
        private readonly CategoryRepositoryInterface $repository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function __invoke(ActionInterface $action): void
    {
        $categories = $this->createCategoryObjects($action->userId);
        foreach ($categories as $category) {
            $this->repository->queueForSaving($category);
        }

        $this->repository->saveChanges();

        $this->eventDispatcher->dispatch(
            new DefaultCategoriesCreatedEvent(
                $action->userId,
                $action->passwordTransientId,
            )
        );
    }

    /**
     * @return Category[]
     */
    private function createCategoryObjects(User $user): array
    {
        $categories = [
            ['name' => 'Personal', 'description' => 'Stories about your experiences, passions and ambitions'],
            ['name' => 'Food', 'description' => 'Food journaling for reaching healthy eating goals'],
            ['name' => 'Work', 'description' => 'Meeting notes, deadlines, countless other bits of information that are best stored here instead of your brain'],
        ];

        $mappedCategories = [];
        foreach ($categories as $category) {
            $category = new Category();
            $category->setName($category['name'])
                ->setDescription($category['description'])
                ->setPosition(++$this->categoryCount)
                ->se;

            $mappedCategories[] = $category;
        }

        return $mappedCategories;
    }
}