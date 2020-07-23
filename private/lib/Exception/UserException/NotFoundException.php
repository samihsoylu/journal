<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class NotFoundException extends UserException
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function entityNameNotFound(string $entityTitle, string $entityName, int $code = 404): self
    {
        return new self("{$entityTitle} with name {$entityName} was not found", $code);
    }

    public static function entityIdNotFound(string $entityTitle, int $entityId, int $code = 404): self
    {
        return new self("{$entityTitle} with id={$entityId} was not found", $code);
    }
}
