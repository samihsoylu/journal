<?php

declare(strict_types=1);

namespace App\Service\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

final readonly class RichContentSanitizer
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = new HtmlSanitizerConfig()
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowMediaSchemes(['http', 'https', 'data', 'blob'])
            ->allowElement('iframe', ['src', 'title', 'width', 'height', 'allowfullscreen'])
            ->forceAttribute('iframe', 'sandbox', 'allow-scripts allow-presentation')
            ->forceAttribute('iframe', 'referrerpolicy', 'no-referrer')
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->allowElement('video', ['src', 'controls', 'width', 'height', 'poster'])
            ->allowElement('audio', ['src', 'controls'])
            ->allowElement('source', ['src', 'type'])
            ->allowAttribute('class', ['figure', 'img'])
            ->allowAttribute('style', ['p', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'img', 'figure', 'figcaption', 'ul', 'ol', 'li'])
            ->withAttributeSanitizer(new RichContentAttributeSanitizer())
            // Existing encrypted entries may be much larger than the default 20KB.
            ->withMaxInputLength(-1)
        ;
        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitize(string $html) : string
    {
        return $this->sanitizer->sanitize($html);
    }
}
