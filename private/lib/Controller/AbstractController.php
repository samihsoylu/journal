<?php declare(strict_types=1);

namespace App\Controller;

use Jenssegers\Blade\Blade;

abstract class AbstractController
{
    protected Blade $template;
    protected array $templateParams;

    public function __construct()
    {
        $this->template = new Blade([TEMPLATE_PATH],TEMPLATE_CACHE_PATH);
        $this->templateParams = [
            'assets_url' => ASSETS_URL,
        ];
    }
}