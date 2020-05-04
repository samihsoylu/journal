<?php declare(strict_types=1);

namespace App\Database\Repositories;

use App\Database\Models\Entry;

class EntryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Entry::class;
}
