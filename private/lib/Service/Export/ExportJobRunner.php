<?php

declare(strict_types=1);

namespace App\Service\Export;

use Defuse\Crypto\Key;

interface ExportJobRunner
{
    public function start(int $userId, string $username, Key $key) : int;

    public function isRunning(int $userId, string $username) : bool;
}
