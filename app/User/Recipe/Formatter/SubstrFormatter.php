<?php
declare(strict_types=1);

namespace App\User\Recipe\Formatter;

use Megio\Collection\Formatter\Base\BaseFormatter;
use Megio\Collection\ReadBuilder\Column\Base\ShowOnlyOn;

use function assert;
use function is_string;
use function mb_strlen;
use function mb_substr;

class SubstrFormatter extends BaseFormatter
{
    public function __construct(
        protected ?ShowOnlyOn $showOnlyOn = null,
        private readonly int $length = 12,
    ) {
        parent::__construct($showOnlyOn);
    }

    public function format(
        mixed $value,
        string $key,
    ): string {
        if ($value === null) {
            return '';
        }

        assert(is_string($value) === true);

        if ((mb_strlen($value) <= $this->length) === true) {
            return $value;
        }

        return mb_substr($value, 0, $this->length) . '...';
    }
}
