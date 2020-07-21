<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidArgumentException extends UserException
{
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function incorrectLogin($code = 406): self
    {
        return new self("Username or password is incorrect", $code);
    }
}
