<?php
declare(strict_types=1);

namespace App\App\Mail;

use Latte\Loaders\FileLoader;

use function ltrim;
use function str_starts_with;

/**
 * Custom Latte FileLoader for email templates.
 *
 * Paths starting with '/' are resolved relative to base directory.
 * Other paths use standard Latte behavior (relative to current file).
 */
final class EmailTemplateFileLoader extends FileLoader
{
    public function getReferredName(string $file, string $referringFile): string
    {
        // If path starts with '/', treat as absolute from base directory
        if (str_starts_with($file, '/') === true) {
            return ltrim($file, '/');
        }

        // Otherwise use standard Latte behavior (relative to current template)
        return parent::getReferredName($file, $referringFile);
    }
}
