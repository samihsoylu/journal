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
        } finally {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($directory);
        }
    }
}
