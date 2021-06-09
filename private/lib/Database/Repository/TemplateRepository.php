<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Template;
use App\Database\Model\User;

class TemplateRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    public const RESOURCE_NAME = Template::class;

    /**
     * Queries the database for a list of templates that were created by the provided user
     *
     * @param User $user
     * @return Template[]
     */
    public function findByUser(User $user): array
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['referencedUser' => $user]);
    }
}