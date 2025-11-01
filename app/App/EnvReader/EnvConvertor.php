<?php
declare(strict_types=1);

namespace App\App\EnvReader;

use InvalidArgumentException;

use function is_bool;
use function is_int;
use function is_string;
use function strtolower;

class EnvConvertor
{
    public static function toString(mixed $value): string
    {
        if (is_string($value) === false) {
            throw new InvalidArgumentException("Value is not a string.");
        }

        return $value;
    }

    public static function toInt(mixed $value): int
    {
        if (is_int($value) === false) {
            throw new InvalidArgumentException("Value is not an integer.");
        }

        return $value;
    }

    public static function toBool(mixed $value): bool
    {
        if (is_string($value) === true) {
            $lower = strtolower($value);
            if ($lower === 'true' || $lower === '1') {
                return true;
            }
            if ($lower === 'false' || $lower === '0') {
                return false;
            }
        }

        if (is_int($value) === true) {
            return $value !== 0;
        }

        if (is_bool($value) === true) {
            return $value;
        }

        throw new InvalidArgumentException("Value is not a boolean.");
    }
}
