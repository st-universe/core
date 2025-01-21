<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

class IonStormDataTest extends StuTestCase
{
    public static function getDataProvider(): array
    {
        return [
            [0, 1, 0, 1],
            [45, 1, 1, 1],
            [90, 1, 1, 0],
            [135, 1, 1, -1],
            [180, 1, 0, -1],
            [225, 1, -1, -1],
            [270, 1, -1, 0],
            [315, 1, -1, 1],
            [360, 1, 0, 1],
            [0, 2, 0, 2],
            [45, 2, 1, 1],
            [90, 2, 2, 0],
            [135, 2, 1, -1],
            [180, 2, 0, -2],
            [225, 2, -1, -1],
            [270, 2, -2, 0],
            [315, 2, -1, 1],
            [360, 2, 0, 2]
        ];
    }

    #[DataProvider('getDataProvider')]
    public function testMoveStormExpectMovementChangeWhenTypeVariable(
        int $directionInDegrees,
        int $velocity,
        int $expectedHorizontal,
        int $expectedVertical
    ): void {

        $subject = new IonStormData($directionInDegrees, $velocity);

        $this->assertEquals($expectedHorizontal, $subject->getHorizontalMovement());
        $this->assertEquals($expectedVertical, $subject->getVerticalMovement());
    }
}
