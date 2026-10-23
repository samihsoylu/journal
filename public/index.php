<?php

declare(strict_types=1);

use App\Framework\Kernel;
use App\Router;

require_once dirname(__DIR__) . '/private/init.php';

/** @var Kernel $kernel */
Router::route($kernel->getContainer());
