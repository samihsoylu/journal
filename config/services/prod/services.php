<?php

declare(strict_types=1);

use App\Service\Export\ExportArchiveStore;
use App\Service\Export\ExportJobRunner;
use App\Service\Security\UserKeys;
use App\Utility\BladeRenderer;
use App\Utility\Encryptor;
use App\Utility\Export\BackgroundExportJobRunner;
use App\Utility\Export\FilesystemExportArchiveStore;
use App\Utility\TemplateCacheInterface;
use App\Utility\TemplateRendererInterface;
use DI\Container;

return static function (Container $container) : void {
    $container->set(TemplateRendererInterface::class, DI\get(BladeRenderer::class));
    $container->set(TemplateCacheInterface::class, DI\get(BladeRenderer::class));
    $container->set(UserKeys::class, DI\get(Encryptor::class));
    $container->set(ExportArchiveStore::class, DI\get(FilesystemExportArchiveStore::class));
    $container->set(ExportJobRunner::class, DI\get(BackgroundExportJobRunner::class));
};
