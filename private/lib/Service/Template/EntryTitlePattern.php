<?php

declare(strict_types=1);

namespace App\Service\Template;

use App\Exception\UserException\InvalidParameterException;
use DateTimeImmutable;

final class EntryTitlePattern
{
    public function normalize(?string $pattern, string $templateTitle) : ?string
    {
        if ($pattern === null || trim($pattern) === '') {
            return null;
        }
        $pattern = trim($pattern);
        $expanded = strtr($pattern, ['{date}' => '2000-12-31', '{time}' => '23:59', '{template}' => $templateTitle]);

        if ( ! mb_check_encoding($pattern, 'UTF-8') || strlen($pattern) > 128 || strlen($expanded) < 4 || strlen($expanded) > 128 || preg_match('/[{}]/', str_replace(['{date}', '{time}', '{template}'], '', $pattern))) {
            throw new InvalidParameterException('Use {date}, {time} or {template} in the default title, and keep the result short enough for an entry title.');
        }

        return $pattern;
    }

    public function render(?string $pattern, string $templateTitle, DateTimeImmutable $date) : ?string
    {
        $pattern = $this->normalize($pattern, $templateTitle);

        return $pattern === null ? null : strtr($pattern, ['{date}' => $date->format('Y-m-d'), '{time}' => $date->format('H:i'), '{template}' => $templateTitle]);
    }
}
