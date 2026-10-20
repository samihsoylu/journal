<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Utility\BladeRenderer;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 *
 * @coversNothing
 */
final class BladeRendererTest extends TestCase
{
    #[Test]
    public function layouts_includes_and_content_escaping_keep_their_existing_behavior() : void
    {
        $directory = sys_get_temp_dir() . '/journal-template-' . bin2hex(random_bytes(12));
        mkdir($directory, 0o700);
        mkdir($directory . '/cache', 0o700);

        try {
            file_put_contents($directory . '/layout.blade.php', '<main>@yield("body")</main>');
            file_put_contents($directory . '/heading.blade.php', '<h1>{{ $title }}</h1>');
            file_put_contents($directory . '/page.blade.php', '@extends("layout")@section("body")@include("heading"){!! $content !!}@endsection');
            $renderer = new BladeRenderer($directory, $directory . '/cache');
            self::assertSame(
                '<main><h1>&lt;Private&gt;</h1><p>Entry HTML</p></main>',
                $renderer->render('page', ['title' => '<Private>', 'content' => '<p>Entry HTML</p>']),
            );
            self::assertNotEmpty(glob($directory . '/cache/*.php'));
            file_put_contents($directory . '/cache/keep.txt', 'unrelated file');
            $renderer->clearCache();
            self::assertSame([], glob($directory . '/cache/*.php'));
            self::assertFileExists($directory . '/cache/keep.txt');
            self::assertFileExists($directory . '/page.blade.php');
        } finally {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($directory);
        }
    }
}
