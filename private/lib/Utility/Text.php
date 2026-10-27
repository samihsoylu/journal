<?php

declare(strict_types=1);

namespace App\Utility;

final class Text
{
    public static function containsHtml(string $string) : bool
    {
        return $string !== strip_tags($string);
    }
}
