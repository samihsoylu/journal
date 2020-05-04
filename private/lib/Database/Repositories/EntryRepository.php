<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Entry;

class EntryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Entry::class;
}
