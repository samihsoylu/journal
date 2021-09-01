<?php

namespace App\Utility\Command\Interfaces;

use App\Utility\Command\Command;

interface ProcessInterface
{
    public static function start(Command $command): self;
    public function isRunning(): bool;
    public function stop(): void;
}