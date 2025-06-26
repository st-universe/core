<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\StuTestCase;

class TransferToClosestLocationTest extends StuTestCase
{
    private MockInterface&ClosestLocations $closestLocations;

    private MockInterface&CrewAssignmentRepositoryInterface $shipCrewRepository;

    private MockInterface&DistanceCalculationInterface $distanceCalculation;

    private MockInterface&Ship $ship;
    private MockInterface&Ship $target;
    private int $crewCount;

    private TransferToClosestLocation $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->closestLocations = $this->mock(ClosestLocations::class);
        $this->distanceCalculation = $this->mock(DistanceCalculationInterface::class);
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);

        $this->ship = $this->mock(Ship::class);
        $this->target = $this->mock(Ship::class);

        $this->subject = new TransferToClosestLocation(
            $this->closestLocations,
            $this->distanceCalculation,
            $this->shipCrewRepository
        );
    }

    public static function transferTestData(): array
    {
        return [
            //?coloDistance, ?coloName, ?coloSector, ?stationDistance, ?stationName, ?stationSector, tradePostDistance, tradePostName, tradePostSector, expectedMessage
            [null, null, null, null, null, null, 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an den Handelsposten "TP" (T|P) überstellt'],
            [43,   'C', 'C|C', null, null, null, 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an den Handelsposten "TP" (T|P) überstellt'],
            [42,   'C', 'C|C', null, null, null, 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an die Kolonie "C" (C|C) überstellt'],
            [null, null, null, 43,   'S', 'S|S', 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an den Handelsposten "TP" (T|P) überstellt'],
            [null, null, null, 42,   'S', 'S|S', 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an die Station "S" (S|S) überstellt'],
            [42,   'C', 'C|C', 42,   'S', 'S|S', 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an die Kolonie "C" (C|C) überstellt'],
            [42,   'C', 'C|C', 41,   'S', 'S|S', 42, 'TP', 'T|P', 'Deine Crew wurde geborgen und an die Station "S" (S|S) überstellt'],
            [42,   'C', 'C|C', 41,   'S', 'S|S', 40, 'TP', 'T|P', 'Deine Crew wurde geborgen und an den Handelsposten "TP" (T|P) überstellt'],
        ];
    }

    #[DataProvider('transferTestData')]
    public function testTransfer(
        ?int $coloDistance,
        ?string $coloName,
        ?string $coloSector,
        ?int $stationDistance,
        ?string $stationName,
        ?string $stationSector,
        int $tradePostDistance,
        string $tradePostName,
        string $tradePostSector,
        string $expectedMessage
    ): void {
        $this->crewCount = 5;

        $closestColony = $this->mockClosestUsableColony(
            $coloDistance,
            $coloName,
            $coloSector
        );

        $closestStation = $this->mockClosestUsableStation(
            $stationDistance,
            $stationName,
            $stationSector
        );
        $closestTradepost = $this->mockClosestTradepost(
            $tradePostDistance,
            $tradePostName,
            $tradePostSector
        );

        $this->mockCrewTransfer(
            $coloDistance,
            $stationDistance,
            $tradePostDistance,
            $closestColony,
            $closestStation,
            $closestTradepost
        );

        $result = $this->subject->transfer(
            $this->ship,
            $this->target,
            $this->crewCount,
            $closestTradepost
        );

        $this->assertEquals($expectedMessage, $result);
    }

    private function mockClosestUsableColony(
        ?int $coloDistance,
        ?string $coloName,
        ?string $coloSector
    ): ?Colony {
        $result = null;

        if ($coloDistance !== null) {
            $colony = $this->mock(Colony::class);
            $colony->shouldReceive('getName')
                ->withNoArgs()
                ->andReturn($coloName);
            $colony->shouldReceive('getSectorString')
                ->withNoArgs()
                ->andReturn($coloSector);

            $result = [$coloDistance, $colony];
        }

        $this->closestLocations->shouldReceive('searchClosestUsableColony')
            ->with($this->ship, $this->crewCount)
            ->once()
            ->andReturn($result);

        return $result === null ? null : $result[1];
    }

    private function mockClosestUsableStation(
        ?int $stationDistance,
        ?string $stationName,
        ?string $stationSector
    ): ?Ship {
        $result = null;

        if ($stationDistance !== null) {
            $station = $this->mock(Ship::class);
            $station->shouldReceive('getName')
                ->withNoArgs()
                ->andReturn($stationName);
            $station->shouldReceive('getSectorString')
                ->withNoArgs()
                ->andReturn($stationSector);

            $result = [$stationDistance, $station];
        }

        $this->closestLocations->shouldReceive('searchClosestUsableStation')
            ->with($this->ship, $this->crewCount)
            ->once()
            ->andReturn($result);

        return $result === null ? null : $result[1];
    }

    private function mockClosestTradepost(
        int $tradePostDistance,
        string $tradePostName,
        string $tradePostSector
    ): TradePost {

        $tradepost = $this->mock(TradePost::class);
        $station = $this->mock(Station::class);
        $tradepost->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn($tradePostName);
        $tradepost->shouldReceive('getStation')
            ->withNoArgs()
            ->andReturn($station);
        $station->shouldReceive('getSectorString')
            ->withNoArgs()
            ->andReturn($tradePostSector);

        $this->distanceCalculation->shouldReceive('shipToShipDistance')
            ->with($this->ship, $station)
            ->once()
            ->andReturn($tradePostDistance);

        return $tradepost;
    }

    private function mockCrewTransfer(
        ?int $coloDistance,
        ?int $stationDistance,
        int $tradePostDistance,
        ?Colony $closestColony,
        ?Ship $closestStation,
        TradePost $closestTradepost
    ): void {
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);

        $shipCrew = $this->mock(CrewAssignment::class);
        $foreignCrewAssignment = $this->mock(CrewAssignment::class);
        $crewlist = new ArrayCollection([$shipCrew, $foreignCrewAssignment]);

        $this->target->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->andReturn($crewlist);

        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $shipCrew->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->andReturn($user);
        $foreignCrewAssignment->shouldReceive('getCrew->getUser')
            ->withNoArgs()
            ->andReturn($otherUser);

        $minDistance = min(
            $coloDistance ?? PHP_INT_MAX,
            $stationDistance ?? PHP_INT_MAX,
            $tradePostDistance
        );

        if ($minDistance === $coloDistance) {
            $shipCrew->shouldReceive('setColony')
                ->with($closestColony);
            $shipCrew->shouldReceive('setSpacecraft')
                ->with(null);
        } elseif ($minDistance === $stationDistance) {
            $shipCrew->shouldReceive('setSpacecraft')
                ->with($closestStation);
        } else {
            $shipCrew->shouldReceive('setSpacecraft')
                ->with(null);
            $shipCrew->shouldReceive('setTradepost')
                ->with($closestTradepost);
        }

        $this->shipCrewRepository->shouldReceive('save')
            ->with($shipCrew)
            ->once();
    }
}
