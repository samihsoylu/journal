<?php

declare(strict_types=1);

namespace App\Service\Security;

final class RecoveryCodes
{
    /** @return list<string> */
    public function generate() : array
    {
        $codes = [];

        for ($index = 0; $index < 10; ++$index) {
            $codes[] = implode('-', str_split(bin2hex(random_bytes(16)), 8));
        }

        return $codes;
    }

    public function hash(string $code) : ?string
    {
        if (strlen($code) > 128) {
            return null;
        }
        $canonical = strtolower(str_replace(['-', ' ', "\t", "\r", "\n"], '', $code));

        return preg_match('/^[0-9a-f]{32}$/D', $canonical) === 1 ? hash('sha256', 'journal-recovery:' . $canonical) : null;
    }
}
