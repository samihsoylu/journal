<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Port\Orm;

interface ORMCreatable
{
    public function create(): object;
}