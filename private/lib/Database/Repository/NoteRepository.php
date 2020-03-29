<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Note;

class NoteRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Note::class;
}
