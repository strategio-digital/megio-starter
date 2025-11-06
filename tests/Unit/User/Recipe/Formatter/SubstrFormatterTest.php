<?php
declare(strict_types=1);

namespace Tests\Unit\User\Recipe\Formatter;

use App\User\Recipe\Formatter\SubstrFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SubstrFormatterTest extends TestCase
{
    #[DataProvider('formatDataProvider')]
    public function testFormat(
        ?string $input,
        int $length,
        string $expected,
    ): void {
        $formatter = new SubstrFormatter(length: $length);
        $result = $formatter->format($input, 'test_key');

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{input: string|null, length: int, expected: string}>
     */
    public static function formatDataProvider(): array
    {
        return [
            'null value returns empty string' => [
                'input' => null,
                'length' => 12,
                'expected' => '',
            ],
            'short string unchanged' => [
                'input' => 'Short',
                'length' => 12,
                'expected' => 'Short',
            ],
            'string exactly at limit unchanged' => [
                'input' => 'Exactly12chr',
                'length' => 12,
                'expected' => 'Exactly12chr',
            ],
            'long string truncated with ellipsis' => [
                'input' => 'This is a very long string',
                'length' => 12,
                'expected' => 'This is a ve...',
            ],
            'custom length truncation' => [
                'input' => 'Hello World',
                'length' => 5,
                'expected' => 'Hello...',
            ],
            'multibyte characters truncation' => [
                'input' => 'Příliš žluťoučký kůň',
                'length' => 10,
                'expected' => 'Příliš žlu...',
            ],
            'multibyte characters unchanged when short' => [
                'input' => 'Příliš žluťoučký',
                'length' => 16,
                'expected' => 'Příliš žluťoučký',
            ],
            'emojis handled correctly' => [
                'input' => 'Hello 😀 World 🌍',
                'length' => 5,
                'expected' => 'Hello...',
            ],
        ];
    }
}
