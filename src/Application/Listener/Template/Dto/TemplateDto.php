<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Listener\Template\Dto;

final readonly class TemplateDto
{
    public function __construct(
        public string $title,
        public string $content,
    ) {}
}
