<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Service\Security\RichContentAttributeSanitizer;
use App\Service\Security\RichContentSanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(RichContentSanitizer::class)]
#[CoversClass(RichContentAttributeSanitizer::class)]
final class RichContentSanitizerTest extends TestCase
{
    #[Test]
    #[DataProvider('provideActive_html_and_editor_control_attributes_are_removedCases')]
    public function active_html_and_editor_control_attributes_are_removed(string $html, string $forbidden) : void
    {
        $output = new RichContentSanitizer()->sanitize($html);
        self::assertStringNotContainsString($forbidden, strtolower($output));
    }

    public static function provideActive_html_and_editor_control_attributes_are_removedCases() : iterable
    {
        yield 'script' => ['<script>alert(1)</script><p>Keep me</p>', '<script'];
        yield 'event handler' => ['<img src="/media/pixel" onerror="alert(1)">', 'onerror'];
        yield 'encoded javascript link' => ['<a href="jav&#x61;script:alert(1)">link</a>', 'javascript:'];
        yield 'mixed case event' => ['<img src="/media/pixel" oNlOaD="alert(1)">', 'onload'];
        yield 'frame html' => ['<iframe srcdoc="<script>alert(1)</script>" src="https://example.test/embed"></iframe>', 'srcdoc'];
        yield 'frame data' => ['<iframe src="data:text/html,<script>alert(1)</script>"></iframe>', 'data:text/html'];
        yield 'frame blob' => ['<iframe src="blob:https://example.test/id"></iframe>', 'blob:'];
        yield 'svg' => ['<svg onload="alert(1)"><script>alert(1)</script></svg>', '<svg'];
        yield 'mathml' => ['<math><mtext><img src=x onerror=alert(1)></mtext></math>', 'onerror'];
        yield 'noscript paste' => ['<noscript><img src=x onerror=alert(1)></noscript>', 'onerror'];
        yield 'editor object' => ['<span data-mce-object="iframe" data-mce-html="PHNjcmlwdD4=">text</span>', 'data-mce-'];
        yield 'editor event' => ['<p data-mce-onclick="alert(1)">text</p>', 'data-mce-'];
        yield 'css url' => ['<p style="background-image: url(javascript:alert(1)); color: red">text</p>', 'url('];
        yield 'css overlay' => ['<p style="position: fixed; inset: 0; z-index: 100000; text-align: center">text</p>', 'position'];
        yield 'css escape' => ['<p style="color: e\78pression(alert(1))">text</p>', 'expression'];
        yield 'object' => ['<object data="javascript:alert(1)"></object>', '<object'];
        yield 'form' => ['<form action="https://example.test/steal"><input name="password"></form>', '<form'];
        yield 'base' => ['<base href="https://example.test/"><p>text</p>', '<base'];
        yield 'style sheet' => ['<style>body {display:none}</style>', '<style'];
        yield 'meta redirect' => ['<meta http-equiv="refresh" content="0;url=https://example.test">', '<meta'];
    }

    #[Test]
    public function normal_formatting_local_images_and_sandboxed_embeds_survive() : void
    {
        $html = '<h2>Title</h2><p style="text-align: center; color: #ff0000"><strong>Bold</strong> <em>Italic</em></p><table style="width: 100%; border-collapse: collapse"><tbody><tr><td>Cell</td></tr></tbody></table><img src="/journal/media/abc" width="10"><a href="../entry/1">Read</a><iframe src="https://www.youtube.com/embed/test" width="640" height="360" allowfullscreen></iframe>';
        $output = new RichContentSanitizer()->sanitize($html);

        foreach (['<h2>Title</h2>', '<strong>Bold</strong>', '<em>Italic</em>', 'text-align: center', 'color: #ff0000', '<table', '<td>Cell</td>', '/journal/media/abc', '../entry/1', 'https://www.youtube.com/embed/test', 'sandbox="allow-scripts allow-presentation"', 'rel="noopener noreferrer"'] as $expected) {
            self::assertStringContainsString($expected, $output);
        }
    }

    #[Test]
    public function existing_caption_and_alignment_classes_keep_their_formatting() : void
    {
        $output = new RichContentSanitizer()->sanitize('<figure class="image align-left hidden"><img class="align-right unexpected" src="/media/image" style="float: left; margin-right: 15px"><figcaption>Caption</figcaption></figure>');

        foreach (['class="image align-left"', 'class="align-right"', 'float: left', 'margin-right: 15px', '<figcaption>Caption</figcaption>'] as $expected) {
            self::assertStringContainsString($expected, $output);
        }
        self::assertStringNotContainsString('hidden', $output);
        self::assertStringNotContainsString('unexpected', $output);
    }

    #[Test]
    public function large_existing_entries_and_pasted_image_previews_are_not_truncated() : void
    {
        $text = str_repeat('Private words ', 5000);
        $output = new RichContentSanitizer()->sanitize('<p>' . $text . '</p><img src="blob:https://example.test/id"><img src="data:image/png;base64,aGVsbG8=">');
        self::assertStringContainsString($text, $output);
        self::assertStringContainsString('blob:https://example.test/id', $output);
        self::assertStringContainsString('data:image/png;base64,aGVsbG8=', html_entity_decode($output));
    }

    #[Test]
    public function supported_editor_styles_survive_while_unknown_classes_and_broken_declarations_do_not() : void
    {
        $styles = 'vertical-align: middle; font-weight: 700; font-style: italic; text-decoration: underline line-through; border-style: dashed; list-style-type: lower-roman; padding-left: 12px';
        $output = new RichContentSanitizer()->sanitize('<p class="untrusted" style="broken; ' . $styles . '; position: fixed">Styled text</p>');

        foreach (explode('; ', $styles) as $style) {
            self::assertStringContainsString($style, $output);
        }
        self::assertStringNotContainsString('untrusted', $output);
        self::assertStringNotContainsString('broken', $output);
        self::assertStringNotContainsString('position', $output);
    }
}
