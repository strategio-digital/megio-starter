<?php
declare(strict_types=1);

namespace App\App\EnvReader;

use InvalidArgumentException;

class EnvReader
{
    public static function toString(mixed $value): string
    {
        if (is_string($value) === false) {
            throw new InvalidArgumentException("Value is not a string.");
        }

        return (string)$value;
    }

    public static function toInt(mixed $value): int
    {
        if (is_int($value) === false) {
            throw new InvalidArgumentException("Value is not an integer.");
        }

        return (int)$value;
    }
}
