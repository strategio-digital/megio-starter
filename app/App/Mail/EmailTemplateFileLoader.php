<?php
declare(strict_types=1);

namespace App;

use Latte\Loaders\FileLoader;

/**
 * Custom Latte FileLoader for email templates that supports absolute paths.
 *
 * Paths starting with '/' are treated as absolute relative to the base directory.
 * Other paths use default Latte behavior (relative to current template).
 */
final class EmailTemplateFileLoader extends FileLoader
{
    public function getReferredName(string $file, string $referringFile): string
    {
        // If path starts with '/', treat as absolute from base directory
        if (str_starts_with($file, '/')) {
            return ltrim($file, '/');
        }

        // Otherwise use default behavior (relative to current template)
        return parent::getReferredName($file, $referringFile);
    }
}
