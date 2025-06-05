<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\StuTestCase;

class UpdateFlightDirectionTest extends StuTestCase
{
    private UpdateFlightDirectionInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new UpdateFlightDirection();
    }

    public static function provideTestUpdateData(): array
    {
        return [
            //startX, startY, destX, destY, exception?, flightDirection

            //OK
            [5,         5,      5,      6,      false,      DirectionEnum::BOTTOM],
            [5,         5,      5,      4,      false,      DirectionEnum::TOP],
            [4,         5,      5,      5,      false,      DirectionEnum::RIGHT],
            [5,         5,      4,      5,      false,      DirectionEnum::LEFT],

            //ERROR
            [5,         5,      5,      5,      true,      null],
        ];
    }

    #[DataProvider('provideTestUpdateData')]
    public function testUpdate(
        int $startX,
        int $startY,
        int $destX,
        int $destY,
        bool $expectException,
        ?DirectionEnum $expectedFlightDirection
    ): void {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
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
            $wrapper->shouldReceive('getComputerSystemDataMandatory->setFlightDirection')
                ->with($expectedFlightDirection)
                ->once();
        }

        $result = $this->subject->updateWhenTraversing($oldWaypoint, $waypoint, $wrapper);

        if (!$expectException) {
            $this->assertEquals($expectedFlightDirection, $result);
        }
    }
}
