<?php

declare(strict_types=1);

namespace App\Utility;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

final readonly class BladeRenderer implements TemplateRendererInterface, TemplateCacheInterface
{
    private Factory $views;

    public function __construct(
        string $templatePath = TEMPLATE_PATH,
        private string $cachePath = TEMPLATE_CACHE_PATH,
    ) {
        $files = new Filesystem();
        $compiler = new BladeCompiler($files, $cachePath);
        $engines = new EngineResolver();
        $engines->register('blade', static fn () => new CompilerEngine($compiler, $files));
        $engines->register('php', static fn () => new PhpEngine($files));
        $engines->register('file', static fn () => new FileEngine($files));
        $container = new Container();
        $this->views = new Factory($engines, new FileViewFinder($files, [$templatePath]), new Dispatcher($container));
        $this->views->setContainer($container);
    }

    public function render(string $template, array $variables = []) : string
    {
        return $this->views->make($template, $variables)->render();
    }

    public function clearCache() : void
    {
        foreach (glob($this->cachePath . '/*.php') ?: [] as $path) {
            unlink($path);
        }
    }
}
