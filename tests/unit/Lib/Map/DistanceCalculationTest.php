<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\StuTestCase;

class DistanceCalculationTest extends StuTestCase
{
    private DistanceCalculationInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new DistanceCalculation();
    }

    public static function shipToShipDistanceTestData(): array
    {
        return [
            //?ship1systemX, ?ship1systemY, ship1posX, ship1posY, ?ship2systemX, ?ship2systemY, ship2posX, ship2posY, expectedDistance
            // both outside system
            [null, null, 1, 1, null, null, 1, 1, 0],
            [null, null, 2, 1, null, null, 1, 1, 1000],
            [null, null, 1, 2, null, null, 1, 1, 1000],
            [null, null, 2, 2, null, null, 1, 1, 2000],

            // one inside system
            [1, 1, 1, 1, null, null, 1, 1, 1],
            [1, 1, 1, 2, null, null, 1, 1, 2],
            [1, 1, 2, 2, null, null, 1, 1, 3],
            [1, 1, 19, 20, null, null, 1, 1, 2],
            [1, 1, 19, 19, null, null, 1, 1, 3],
            [1, 1, 1, 1, null, null, 1, 2, 1001],
            [1, 1, 1, 1, null, null, 2, 2, 2001],
            [null, null, 1, 1, 5, 5, 1, 1, 8001],

            // both inside same system
            [1, 1, 1, 1, 1, 1, 1, 1, 0],
            [1, 1, 1, 2, 1, 1, 1, 1, 1],
            [1, 1, 2, 2, 1, 1, 1, 1, 2],
            [1, 1, 20, 20, 1, 1, 1, 1, 38],
            [1, 1, 20, 20, 1, 1, 19, 19, 2],

            // in different systems
            [1, 1, 1, 1, 1, 2, 1, 1, 1002],
            [2, 5, 1, 1, 7, 6, 1, 1, 6002],
            [2, 5, 3, 8, 7, 6, 16, 9, 6023],
            [23, 23, 19, 19, 22, 22, 19, 19, 2006]
        ];
    }

    #[DataProvider('shipToShipDistanceTestData')]
    public function testShipToShipDistance(
        ?int $ship1systemX,
        ?int $ship1systemY,
        int $ship1posX,
        int $ship1posY,
        ?int $ship2systemX,
        ?int $ship2systemY,
        int $ship2posX,
        int $ship2posY,
        int $expectedDistance
    ): void {
        $system1 = $this->mockStarSystem($ship1systemX, $ship1systemY);

        if (
            $ship1systemX === $ship2systemX
            && $ship1systemY === $ship2systemY
        ) {
            $system2 = $system1;
        } else {
            $system2 = $this->mockStarSystem($ship2systemX, $ship2systemY);
        }

        $ship1 = $this->mockShipCoordinates($system1, $ship1posX, $ship1posY);
        $ship2 = $this->mockShipCoordinates($system2, $ship2posX, $ship2posY);

        $result = $this->subject->shipToShipDistance($ship1, $ship2);

        $this->assertEquals($expectedDistance, $result);
    }

    public static function shipToColonyDistanceTestData(): array
    {
        return [
            //?shipSystemX, ?shipSystemY, shipPosX, shipPosY, colonySystemX, colonySystemY, colonyPosX, colonyPosY, expectedDistance

            // both inside same system
            [1, 1, 1, 1, 1, 1, 1, 1, 0],
            [1, 1, 1, 2, 1, 1, 1, 1, 1],
            [1, 1, 2, 2, 1, 1, 1, 1, 2],
            [1, 1, 20, 20, 1, 1, 1, 1, 38],
            [1, 1, 20, 20, 1, 1, 19, 19, 2],

            // ship outside system
            [null, null, 1, 1, 1, 1, 1, 1, 1],
            [null, null, 1, 1, 1, 1, 5, 5, 9],
            [null, null, 1, 1, 5, 5, 1, 1, 8001],
            [null, null, 1, 1, 5, 5, 1, 16, 8005],

            // in different systems
            [1, 1, 1, 1, 1, 2, 1, 1, 1002],
            [2, 5, 1, 1, 7, 6, 1, 1, 6002],
            [2, 5, 3, 8, 7, 6, 16, 9, 6023],
            [23, 23, 19, 19, 22, 22, 19, 19, 2006]
        ];
    }

    #[DataProvider('shipToColonyDistanceTestData')]
    public function testShipToColonyDistance(
        ?int $shipSystemX,
        ?int $shipSystemY,
        int $shipPosX,
        int $shipPosY,
        int $colonySystemX,
        int $colonySystemY,
        int $colonyPosX,
        int $colonyPosY,
        int $expectedDistance
    ): void {
        $system1 = $this->mockStarSystem($shipSystemX, $shipSystemY);

        if (
            $shipSystemX === $colonySystemX
            && $shipSystemY === $colonySystemY
        ) {
            $system2 = $system1;
        } else {
            $system2 = $this->mockStarSystem($colonySystemX, $colonySystemY);
        }

        $ship = $this->mockShipCoordinates($system1, $shipPosX, $shipPosY);
        $colony = $this->mockColonyCoordinates($system2, $colonyPosX, $colonyPosY);

        $result = $this->subject->shipToColonyDistance($ship, $colony);

        $this->assertEquals($expectedDistance, $result);
    }

    private function mockStarSystem(
        ?int $x,
        ?int $y
    ): ?StarSystemInterface {
        if ($x !== null && $y !== null) {
            $system = $this->mock(StarSystemInterface::class);

            $system->shouldReceive('getCx')
                ->withNoArgs()
                ->andReturn($x);
            $system->shouldReceive('getCy')
                ->withNoArgs()
                ->andReturn($y);
            $system->shouldReceive('getMaxX')
                ->withNoArgs()
                ->andReturn(20);
            $system->shouldReceive('getMaxY')
                ->withNoArgs()
                ->andReturn(20);

            return $system;
        }

        if ($x !== null || $y !== null) {
            throw new RuntimeException('no allowed');
        }

        return null;
    }

    private function mockShipCoordinates(
        ?StarSystemInterface $system,
        int $shipPosX,
        int $shipPosY
    ): ShipInterface {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->andReturn($system);
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->andReturn($shipPosX);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->andReturn($shipPosY);

        return $ship;
    }

    private function mockColonyCoordinates(
        StarSystemInterface $system,
        int $x,
        int $y
    ): ColonyInterface {
        $colony = $this->mock(ColonyInterface::class);

        $colony->shouldReceive('getSystem')
            ->withNoArgs()
            ->andReturn($system);
        $colony->shouldReceive('getSx')
            ->withNoArgs()
            ->andReturn($x);
        $colony->shouldReceive('getSy')
            ->withNoArgs()
            ->andReturn($y);

        return $colony;
    }
}
