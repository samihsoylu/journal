<?php

namespace App\Database\Repository;

use App\Database\Model\Note;

class NoteRepository extends AbstractRepository
{
    protected const RESOURCE_NAME = Note::class;
}