<?php
declare(strict_types=1);

namespace App\User\Recipe\Formatter;

use Megio\Collection\Formatter\Base\BaseFormatter;
use Megio\Collection\ReadBuilder\Column\Base\ShowOnlyOn;

use function assert;
use function is_string;
use function strlen;
use function substr;

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

        if (strlen($value) <= $this->length) {
            return $value;
        }

        return substr($value, 0, $this->length) . '...';
    }
}
