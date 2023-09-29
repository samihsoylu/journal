<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Util;

use LogicException;
use SplFileInfo;

/**
 * Utility class that extracts the namespace and class name from a PHP file. Files provided are assumed to adhere to PSR-4
 * autoloader standard, and PSR-12 code quality standard
 */
final class PhpFileParser
{
    /**
     * @throws LogicException when a namespace is not found
     */
    public function getNamespaceFromFile(SplFileInfo $file): string
    {
        preg_match('/namespace\s+([a-zA-Z0-9\\\\_]+);/', $file->getContents(), $matches);

        $namespace = $matches[1] ?? null;
        if ($namespace === null) {
            throw new LogicException(
                "Missing namespace declaration in the file located at '{$file->getRealPath()}'. Ensure that the file contains a valid 'namespace' statement at the top."
            );
        }

        return $namespace;
    }

    /**
     * @throws LogicException when a class name is not found
     */
    public function getClassNameFromFile(SplFileInfo $file): string
    {
        preg_match('/\s+class\s+([a-zA-Z0-9_]+)/', $file->getContents(), $matches);

        $className = $matches[1] ?? null;
        if ($className === null) {
            throw new LogicException(
                "No class name declaration was found in the file located at '{$file->getRealPath()}'. Please ensure that the file contains a valid class definition."
            );
        }

        return $className;
    }

    public function getFullyQualifiedClassName(SplFileInfo $file): string
    {
        $namespace = $this->getNamespaceFromFile($file);
        $className = $this->getClassNameFromFile($file);

        return $namespace . '\\' . $className;
    }
}