<?php

declare(strict_types=1);

namespace App\Service\Security;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

final class RichContentAttributeSanitizer implements AttributeSanitizerInterface
{
    public function getSupportedElements() : ?array
    {
        return null;
    }

    public function getSupportedAttributes() : array
    {
        return ['style', 'src', 'class'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config) : ?string
    {
        if ($attribute === 'class') {
            $classes = array_intersect(preg_split('/\s+/', trim($value)), ['image', 'align-left', 'align-right', 'align-center']);

            return $classes === [] ? null : implode(' ', $classes);
        }

        if ($attribute === 'src') {
            // Data URLs are useful for pasted images, but never for active frames.
            if ($element === 'iframe' && ! preg_match('#\A(?:https?://|//|/|\.{1,2}/)#i', $value)) {
                return null;
            }

            return $value;
        }
        $safe = [];

        foreach (explode(';', $value) as $declaration) {
            $parts = explode(':', $declaration, 2);

            if (count($parts) !== 2) {
                continue;
            }
            [$property, $content] = array_map('trim', $parts);
            $property = strtolower($property);
            $pattern = match ($property) {
                'text-align' => '/\A(?:left|right|center|justify|start|end)\z/i',
                'vertical-align' => '/\A(?:top|middle|bottom|baseline|sub|super)\z/i',
                'color', 'background-color', 'border-color' => '/\A(?:#[a-f0-9]{3,8}|[a-z]{1,24}|rgba?\([\d.,%\s]+\))\z/i',
                'width', 'height', 'max-width', 'min-width', 'font-size', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom', 'padding-left', 'border-width' => '/\A(?:0|auto|\d{1,5}(?:\.\d{1,3})?(?:px|pt|em|rem|%))\z/i',
                'float' => '/\A(?:left|right|none)\z/i',
                'font-weight' => '/\A(?:normal|bold|[1-9]00)\z/i',
                'font-style' => '/\A(?:normal|italic|oblique)\z/i',
                'text-decoration' => '/\A(?:none|underline|line-through|overline)(?:\s+(?:underline|line-through|overline))*\z/i',
                'border-style' => '/\A(?:none|solid|dashed|dotted|double)\z/i',
                'border-collapse' => '/\A(?:collapse|separate)\z/i',
                'list-style-type' => '/\A(?:disc|circle|square|decimal|lower-alpha|upper-alpha|lower-roman|upper-roman|none)\z/i',
                default => null,
            };

            if ($pattern !== null && preg_match($pattern, $content)) {
                $safe[] = $property . ': ' . $content;
            }
        }

        return $safe === [] ? null : implode('; ', $safe);
    }
}
