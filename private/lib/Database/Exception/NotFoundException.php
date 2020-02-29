<?php

namespace App\Database\Exception;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function entityNameNotFound($entityTitle, $entityName, $code = 404)
    {
        return new self("{$entityTitle} with name {$entityName} was not found", $code);
    }

    public static function entityIdNotFound($entityTitle, $entityId, $code = 404)
    {
        return new self("{$entityTitle} with id={$entityId} was not found", $code);
    }
}