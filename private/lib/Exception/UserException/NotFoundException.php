<?php

declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

final class NotFoundException extends UserException
{
    public static function entityNameNotFound(string $entityTitle, string $entityName, int $code = 404) : self
    {
        return new self(sprintf('%s with name %s was not found', $entityTitle, $entityName), $code);
    }

    public static function entityIdNotFound(string $entityTitle, int $entityId, int $code = 404) : self
    {
        return new self(sprintf("%s with id '%d' was not found", $entityTitle, $entityId), $code);
    }
}
