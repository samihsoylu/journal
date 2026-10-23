<?php

declare(strict_types=1);

namespace App\Service\Entry;

use App\Exception\UserException\InvalidParameterException;
use DateTimeImmutable;
use DateTimeZone;

final class EntryDateTime
{
    public function parse(?string $value, ?string $timezone) : ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ( ! preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}\z/', $value)) {
            throw InvalidParameterException::invalidDateFormat('entry_date_time');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, new DateTimeZone($timezone ?? 'UTC'));
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d\TH:i') !== $value || $date->getTimestamp() < -2147483648 || $date->getTimestamp() > 2147483647) {
            throw InvalidParameterException::invalidDateFormat('entry_date_time');
        }

        return $date->getTimestamp();
    }

    public function format(int $timestamp, ?string $timezone) : string
    {
        return new DateTimeImmutable('@' . $timestamp)->setTimezone(new DateTimeZone($timezone ?? 'UTC'))->format('Y-m-d\TH:i');
    }
}
