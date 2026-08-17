<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GradientColorTest extends TestCase
{
    #[DataProvider('zeroRangeDataProvider')]
    public function testCalculateGradientColorWithZeroRange(int $modificator, string $expected): void
    {
        $subject = new GradientColor();

        self::assertSame($expected, $subject->calculateGradientColor($modificator, 0, 0));
    }

    /** @return array<string, array{int, string}> */
    public static function zeroRangeDataProvider(): array
    {
        return [
            'at lower limit' => [0, '#00ff00'],
            'above lower limit' => [1, '#ff0000']
        ];
    }
}
