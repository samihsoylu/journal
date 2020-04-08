<?php declare(strict_types=1);

namespace App\Controller;

use Jenssegers\Blade\Blade;

abstract class AbstractController
{
    protected Blade $template;

    public function __construct()
    {
        $this->template = new Blade([TEMPLATE_PATH],TEMPLATE_CACHE_PATH);
    }
}