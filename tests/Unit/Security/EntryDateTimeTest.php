<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidParameterException;
use App\Service\Entry\EntryDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(EntryDateTime::class)]
final class EntryDateTimeTest extends TestCase
{
    #[Test]
    public function an_omitted_date_preserves_the_default_creation_or_edit_behavior() : void
    {
        self::assertNull(new EntryDateTime()->parse(null, null));
        self::assertNull(new EntryDateTime()->parse('', 'Europe/Amsterdam'));
    }

    #[Test]
    public function local_times_are_stored_as_unix_time_in_the_account_timezone() : void
    {
        $dates = new EntryDateTime();
        self::assertSame(1704033000, $dates->parse('2023-12-31T23:30', 'Asia/Tokyo'));
        self::assertSame('2023-12-31T23:30', $dates->format(1704033000, 'Asia/Tokyo'));
        self::assertSame(1704110400, $dates->parse('2024-01-01T12:00', null));
        self::assertSame('2024-01-01T12:00', $dates->format(1704110400, null));
        self::assertSame(-86400, $dates->parse('1969-12-31T00:00', 'UTC'));
    }

    #[Test]
    #[DataProvider('provideImpossible_input_formats_and_out_of_storage_range_dates_are_rejectedCases')]
    public function impossible_input_formats_and_out_of_storage_range_dates_are_rejected(string $date, string $timezone) : void
    {
        $this->expectException(InvalidParameterException::class);
        new EntryDateTime()->parse($date, $timezone);
    }

    public static function provideImpossible_input_formats_and_out_of_storage_range_dates_are_rejectedCases() : iterable
    {
        foreach (['2023-02-29T12:00', '2024-02-30T12:00', '2024-13-01T00:00', '2024-01-01T25:00', '2024-01-01T12:60', '2024-01-01', 'today', '2024-01-01T12:00:00', "2024-01-01T12:00\0", '1800-01-01T00:00', '2039-01-01T00:00'] as $index => $date) {
            yield 'invalid-' . $index => [$date, 'UTC'];
        }
        yield 'nonexistent spring daylight saving hour' => ['2024-03-31T02:30', 'Europe/Amsterdam'];
    }
}
