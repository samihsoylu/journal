<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidParameterException;
use App\Service\Security\ImageContentValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(ImageContentValidator::class)]
final class ImageContentValidatorTest extends TestCase
{
    private const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9Zl1sAAAAASUVORK5CYII=';

    #[Test]
    #[DataProvider('provideNon_images_and_oversized_uploads_are_rejectedCases')]
    public function non_images_and_oversized_uploads_are_rejected(string $binary, int $limit) : void
    {
        $this->expectException(InvalidParameterException::class);
        new ImageContentValidator()->validate($binary, $limit);
    }

    public static function provideNon_images_and_oversized_uploads_are_rejectedCases() : iterable
    {
        yield 'empty' => ['', 100];
        yield 'html renamed png' => ['<script>alert(1)</script>', 1000];
        yield 'svg active content' => ['<svg onload="alert(1)"></svg>', 1000];
        yield 'png signature only' => ["\x89PNG\r\n\x1a\n", 1000];
        yield 'too large' => [base64_decode(self::PNG, true), 10];
    }

    #[Test]
    public function the_type_is_derived_from_the_image_data() : void
    {
        self::assertSame('png', new ImageContentValidator()->validate(base64_decode(self::PNG, true), 1000));
        self::assertSame('gif', new ImageContentValidator()->validate(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', true), 1000));
    }
}
