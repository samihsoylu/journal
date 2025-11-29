<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/private/init.php';

// @var App\Framework\Kernel $kernel

App\Router::route($kernel->getContainer());
