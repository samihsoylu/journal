<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidParameterException;
use App\Service\Template\EntryTitlePattern;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(EntryTitlePattern::class)]
final class EntryTitlePatternTest extends TestCase
{
    #[Test]
    public function an_empty_pattern_keeps_manual_titles_and_placeholders_use_the_selected_local_date() : void
    {
        $pattern = new EntryTitlePattern();
        $date = new DateTimeImmutable('2024-02-29T23:30:00+09:00');
        self::assertNull($pattern->normalize(null, 'Sleep'));
        self::assertNull($pattern->render('   ', 'Sleep', $date));
        self::assertSame('Sleep 2024-02-29 23:30', $pattern->render(' {template} {date} {time} ', 'Sleep', $date));
        self::assertSame('Café 2024-02-29', $pattern->render('Café {date}', 'Sleep', $date));
        self::assertSame('2024-02-29 / 2024-02-29', $pattern->render('{date} / {date}', 'Sleep', $date));
        self::assertSame('A literal title', $pattern->render('A literal title', 'Sleep', $date));
    }

    #[Test]
    #[DataProvider('provideUnsafe_or_invalid_patterns_are_validation_errorsCases')]
    public function unsafe_or_invalid_patterns_are_validation_errors(string $pattern, string $title) : void
    {
        $this->expectException(InvalidParameterException::class);
        new EntryTitlePattern()->normalize($pattern, $title);
    }

    public static function provideUnsafe_or_invalid_patterns_are_validation_errorsCases() : iterable
    {
        yield 'unknown placeholder' => ['Sleep {day}', 'Sleep'];
        yield 'broken placeholder' => ['Sleep {date', 'Sleep'];
        yield 'blade expression is not executed' => ['{{ system("id") }}', 'Sleep'];
        yield 'php expression is not executed' => ['{${system("id")}}', 'Sleep'];
        yield 'overlong literal' => [str_repeat('a', 129), 'Sleep'];
        yield 'expansion exceeds title limit' => ['{template} {date}', str_repeat('a', 128)];
        yield 'too short' => ['abc', 'Sleep'];
        yield 'malformed utf8' => ["abc\xff", 'Sleep'];
    }

    #[Test]
    public function html_is_plain_text_for_the_title_input() : void
    {
        self::assertSame('<script>literal</script>', new EntryTitlePattern()->render('<script>literal</script>', 'Sleep', new DateTimeImmutable('2024-01-01')));
    }
}
