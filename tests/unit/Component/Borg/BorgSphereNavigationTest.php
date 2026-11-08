<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BorgSphereNavigationTest extends TestCase
{
    /** @return iterable<string, array{int, int, int, bool}> */
    public static function radiusPositions(): iterable
    {
        yield 'east boundary' => [20, 0, 20, true];
        yield 'circular boundary' => [12, 16, 20, true];
        yield 'negative circular boundary' => [-12, -16, 20, true];
        yield 'diagonal outside circle' => [15, 15, 20, false];
        yield 'one field outside edge' => [20, 1, 20, false];
        yield 'beyond boundary' => [21, 0, 20, false];
        yield 'only origin at zero radius' => [0, 0, 0, true];
        yield 'negative radius' => [0, 0, -1, false];
    }

    #[DataProvider('radiusPositions')]
    public function testRoamingUsesCircularDistanceFromTheAnchoredSystem(int $dx, int $dy, int $radius, bool $expected): void
    {
        self::assertSame($expected, BorgSphereNavigation::isWithinRadius($dx, $dy, $radius));
    }
}
