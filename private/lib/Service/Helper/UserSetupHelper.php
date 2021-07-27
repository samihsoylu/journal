<?php

namespace App\Service\Helper;

use App\Database\Model\Category;
use App\Database\Model\Template;
use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\UserRepository;
use App\Utility\Registry;
use Defuse\Crypto\Key;

class UserSetupHelper
{
    private UserRepository $repository;
    private CategoryRepository $categoryRepository;
    private User $user;

    private const PERSONAL_CATEGORY = 'Personal';
    private const FOOD_CATEGORY = 'Food';
    private const WORK_CATEGORY = 'Work';

    public function __construct(User $user)
    {
        $this->user = $user;

        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);
        $this->repository = $repository;

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = Registry::get(CategoryRepository::class);
        $this->categoryRepository = $categoryRepository;
    }

    public function createDefaultCategories()
    {
        $personal = new Category();
        $personal->setName(self::PERSONAL_CATEGORY);
        $personal->setDescription('Stories about your experiences, passions and ambitions');
        $personal->setReferencedUser($this->user);
        $personal->setSortOrder(1);
        $this->repository->queue($personal);

        $diet = new Category();
        $diet->setName(self::FOOD_CATEGORY);
        $diet->setDescription('Food journaling for reaching healthy eating goals');
        $diet->setReferencedUser($this->user);
        $diet->setSortOrder(2);
        $this->repository->queue($diet);

        $work = new Category();
        $work->setName(self::WORK_CATEGORY);
        $work->setDescription('Meeting notes, deadlines, countless other bits of information that are best stored here instead of your brain');
        $work->setReferencedUser($this->user);
        $work->setSortOrder(3);
        $this->repository->queue($work);

        $this->repository->save();
    }

    public function createDefaultTemplates(Key $key)
    {
        $foodCategory = $this->categoryRepository->findByCategoryName($this->user, self::FOOD_CATEGORY);
        if ($foodCategory === null) {
            throw new \RuntimeException(
                'Could not generate default templates, category ' . self::FOOD_CATEGORY . ' was not found.'
            );
        }

        $food = new Template();
        $food->setTitle('Food tracking')
            ->setContentAndEncrypt("# Breakfast\n\n* ...\n* ...\n* ...\n\n# Lunch\n\n* ...\n* ...\n* ...\n\n# Dinner\n\n* ...\n* ...\n* ...\n\n", $key)
            ->setReferencedCategory($foodCategory)
            ->setReferencedUser($this->user)
            ->save();
    }
}
