<?php

declare(strict_types=1);

namespace App\Database\Migration;

use App\Database\Database;
use App\Database\Model\Category;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\UserRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210627094934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE categories ADD sortOrder INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE categories DROP sortOrder');
    }

    public function postUp(Schema $schema): void
    {
        $database = Database::getInstance();
        $cache = $database->getEntityManager()->getConfiguration()->getMetadataCache();
        if ($cache !== null) {
            $cache->clear();
        }

        $this->setOrderForExistingCategories();
    }

    private function setOrderForExistingCategories(): void
    {
        $userRepository = new UserRepository();
        $users = $userRepository->getAll();

        $categoryRepository = new CategoryRepository();

        foreach ($users as $user) {
            $categories = $categoryRepository->findByUser($user);

            // Sort categories by id in ASC order
            usort($categories, function (Category $a, Category $b) {
                return ($a->getId() > $b->getId());
            });

            for ($i = 0; $i < count($categories); $i++) {
                $category = $categories[$i];
                $category->setSortOrder($i + 1);

                $categoryRepository->queue($category);
            }
        }

        $categoryRepository->save();
    }
}
