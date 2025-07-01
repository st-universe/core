<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\LssBlockade;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

class LssBlockadeGridTest extends StuTestCase
{
    public function testObserverCellAlwaysVisible(): void
    {
        $g = new LssBlockadeGrid(0, 10, 0, 10, 5, 5);
        self::assertTrue($g->isVisible(5, 5));
    }

    public function testFreeLineIsVisible(): void
    {
        $g = new LssBlockadeGrid(0, 10, 0, 10, 5, 5);
        self::assertTrue($g->isVisible(8, 8));   // keinerlei Blocker
    }
    public function testDirectBlockerShadowsLine(): void
    {
        $g = new LssBlockadeGrid(0, 10, 0, 10, 5, 5);
        $g->setBlocked(7, 5);              // Blocker östlich

        self::assertFalse($g->isVisible(7, 5));  // Blocker selbst
        self::assertFalse($g->isVisible(8, 5));  // direkt dahinter
        self::assertFalse($g->isVisible(9, 5));  // weiter dahinter

        self::assertTrue($g->isVisible(5, 5));   // Observer
        self::assertTrue($g->isVisible(4, 5));   // entgegengesetzt
    }

    public function testShadowConeCoversAdjacentTiles(): void
    {
        $g = new LssBlockadeGrid(0, 10, 0, 10, 5, 5);
        $g->setBlocked(7, 5); // Blocker rechts

        self::assertFalse($g->isVisible(8, 6));  // unten‑rechts hinter Kegel
        self::assertFalse($g->isVisible(8, 4));  // oben‑rechts hinter Kegel
        self::assertTrue($g->isVisible(8, 7));   // außerhalb Kegel
    }

    public function testCornerPeekIsBlocked(): void
    {
        $g = new LssBlockadeGrid(0, 6, 0, 6, 3, 3);
        $g->setBlocked(2, 3);  // links
        $g->setBlocked(3, 2);  // oben
        $g->setBlocked(2, 2);  // links‑oben Ecke

        self::assertFalse($g->isVisible(1, 1));
    }

    public function testRectangularGridOffset(): void
    {
        $g = new LssBlockadeGrid(92, 102, 73, 80, 97, 76); // 11×8, Observer (97|76)

        self::assertTrue($g->isVisible(102, 80));

        $g->setBlocked(99, 76);
        self::assertFalse($g->isVisible(101, 76));

        self::assertFalse($g->isVisible(103, 76));
    }

    #[DataProvider('cornerPeekProvider')]
    public function testNoCornerPeekingDataDriven(
        int $blockX1,
        int $blockY1,
        int $blockX2,
        int $blockY2,
        int $targetX,
        int $targetY
    ): void {
        $g = new LssBlockadeGrid(0, 4, 0, 4, 2, 2); // 5×5

        $g->setBlocked($blockX1, $blockY1);
        $g->setBlocked($blockX2, $blockY2);

        self::assertFalse($g->isVisible($targetX, $targetY));
    }

    public static function cornerPeekProvider(): iterable
    {
        return [
            'NW‑Peek' => [2, 1, 1, 2, 1, 1],
            'NE‑Peek' => [2, 1, 3, 2, 3, 1],
            'SW‑Peek' => [2, 3, 1, 2, 1, 3],
            'SE‑Peek' => [2, 3, 3, 2, 3, 3],
        ];
    }
}
