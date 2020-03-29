<?php declare(strict_types=1);

namespace App\Database\Model;

interface ModelInterface
{
    public function getId(): int;
    public function getLastUpdatedTimestamp(): int;
    public function setCreatedTimestamp(): void;
    public function setLastUpdatedTimestamp(): void;
}
