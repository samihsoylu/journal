<?php

declare(strict_types=1);

namespace App\Utility;

interface TemplateCacheInterface
{
    public function clearCache() : void;
}
