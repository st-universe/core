<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class UpdateFlightDirectionTest extends StuTestCase
{
    private UpdateFlightDirectionInterface $subject;

    protected function setUp(): void
    {
        $this->subject = new UpdateFlightDirection();
    }

    public static function provideTestUpdateData()
    {
        return [
            //startX, startY, destX, destY, exception?, flightDirection

            //OK
            [5,         5,      5,      6,      false,      ShipEnum::DIRECTION_BOTTOM],
            [5,         5,      5,      4,      false,      ShipEnum::DIRECTION_TOP],
            [4,         5,      5,      5,      false,      ShipEnum::DIRECTION_RIGHT],
            [5,         5,      4,      5,      false,      ShipEnum::DIRECTION_LEFT],

            //ERROR
            [5,         5,      5,      5,      true,      null],
        ];
    }

    /**
     * @dataProvider provideTestUpdateData
     */
    public function testUpdate(
        int $startX,
        int $startY,
        int $destX,
        int $destY,
        bool $expectException,
        ?int $expectedFlightDirection
    ): void {
        $ship = $this->mock(ShipInterface::class);
        $oldWaypoint = $this->mock(MapInterface::class);
        $waypoint = $this->mock(MapInterface::class);

        $oldWaypoint->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($startX);
        $oldWaypoint->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($startY);

        $waypoint->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($destX);
        $waypoint->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($destY);

        if ($expectException) {
            static::expectExceptionMessage('this should not happen');
            static::expectException(RuntimeException::class);
        } else {
            $ship->shouldReceive('setFlightDirection')
                ->with($expectedFlightDirection)
                ->once();
        }

        $result = $this->subject->updateWhenTraversing($oldWaypoint, $waypoint, $ship);

        if (!$expectException) {
            $this->assertEquals($expectedFlightDirection, $result);
        }
    }
}
