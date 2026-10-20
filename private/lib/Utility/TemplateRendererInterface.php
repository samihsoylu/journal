<?php

declare(strict_types=1);

namespace App\Utility;

interface TemplateRendererInterface
{
    public function render(string $template, array $variables = []) : string;
}
