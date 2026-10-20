<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\Category;
use App\Database\Model\Template;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\TemplateRepository;
use Defuse\Crypto\Key;
use RuntimeException;

final readonly class UserSetupHelper
{
    private const string PERSONAL_CATEGORY = 'Personal';
    private const string FOOD_CATEGORY = 'Food';
    private const string WORK_CATEGORY = 'Work';

    public function __construct(
        private CategoryRepository $categoryRepository,
        private TemplateRepository $templateRepository,
    ) {}

    public function setDefaults(User $user, Key $key) : void
    {
        $this->createDefaultCategories($user);
        $this->createDefaultTemplates($user, $key);
    }

    private function createDefaultCategories(User $user) : void
    {
        $personal = new Category();
        $personal->setName(self::PERSONAL_CATEGORY);
        $personal->setDescription('Stories about your experiences, passions and ambitions');
        $personal->setReferencedUser($user);
        $personal->setSortOrder(1);

        $this->categoryRepository->queue($personal);

        $diet = new Category();
        $diet->setName(self::FOOD_CATEGORY);
        $diet->setDescription('Food journaling for reaching healthy eating goals');
        $diet->setReferencedUser($user);
        $diet->setSortOrder(2);

        $this->categoryRepository->queue($diet);

        $work = new Category();
        $work->setName(self::WORK_CATEGORY);
        $work->setDescription('Meeting notes, deadlines, countless other bits of information that are best stored here instead of your brain');
        $work->setReferencedUser($user);
        $work->setSortOrder(3);

        $this->categoryRepository->queue($work);

        $this->categoryRepository->save();
    }

    private function createDefaultTemplates(User $user, Key $key) : void
    {
        $foodCategory = $this->categoryRepository->findByCategoryName($user, self::FOOD_CATEGORY);

        if ( ! $foodCategory instanceof Category) {
            throw new RuntimeException(
                'Could not generate default templates, category ' . self::FOOD_CATEGORY . ' was not found.',
            );
        }

        $food = new Template();
        $food->setTitle('Food tracking')
            ->setContentAndEncrypt(
                "# Breakfast\n\n* ...\n* ...\n* ...\n\n# Lunch\n\n* ...\n* ...\n* ...\n\n# Dinner\n\n* ...\n* ...\n* ...\n\n",
                $key,
            )
            ->setReferencedCategory($foodCategory)
            ->setReferencedUser($user)
        ;

        $this->templateRepository->queue($food);
        $this->templateRepository->save();
    }
}
