<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SalvageEmergencyPods;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\StuTestCase;

class ClosestLocationsTest extends StuTestCase
{
    private MockInterface&DistanceCalculationInterface $distanceCalculation;
    private MockInterface&StationRepositoryInterface $stationRepository;
    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;

    private MockInterface&Ship $ship;

    private ClosestLocations $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->distanceCalculation = $this->mock(DistanceCalculationInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);

        $this->ship = $this->mock(Ship::class);

        $this->subject = new ClosestLocations(
            $this->distanceCalculation,
            $this->stationRepository,
            $this->troopTransferUtility,
            $this->colonyLibFactory
        );
    }

    public function testSearchClosestUsableStationExpectNullWhenUserHasNoStations(): void
    {
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->stationRepository->shouldReceive('getStationsByUser')
            ->with(5)
            ->andReturn([]);

        $result = $this->subject->searchClosestUsableStation(
            $this->ship,
            42
        );

        $this->assertNull($result);
    }

    public function testSearchClosestUsableStationExpectClosestStation(): void
    {
        $stationUnmanned = $this->mock(Ship::class);
        $stationFarAway = $this->mock(Ship::class);
        $stationClosest = $this->mock(Ship::class);
        $stationNotEnoughSpace = $this->mock(Ship::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->stationRepository->shouldReceive('getStationsByUser')
            ->with(5)
            ->andReturn([$stationUnmanned, $stationFarAway, $stationClosest, $stationNotEnoughSpace]);

        $stationUnmanned->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(false);
        $stationFarAway->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(true);
        $stationClosest->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(true);
        $stationNotEnoughSpace->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->andReturn(true);

        $this->troopTransferUtility->shouldReceive('getFreeQuarters')
            ->with($stationFarAway)
            ->andReturn(42);
        $this->troopTransferUtility->shouldReceive('getFreeQuarters')
            ->with($stationClosest)
            ->andReturn(42);
        $this->troopTransferUtility->shouldReceive('getFreeQuarters')
            ->with($stationNotEnoughSpace)
            ->andReturn(41);

        $this->distanceCalculation->shouldReceive('spacecraftToSpacecraftDistance')
            ->with($this->ship, $stationFarAway)
            ->andReturn(1000);
        $this->distanceCalculation->shouldReceive('spacecraftToSpacecraftDistance')
            ->with($this->ship, $stationClosest)
            ->andReturn(999);

        $result = $this->subject->searchClosestUsableStation(
            $this->ship,
            42
        );

        $this->assertNotNull($result);
        $this->assertEquals(999, $result[0]);
        $this->assertEquals($stationClosest, $result[1]);
    }

    public function testSearchClosestUsableColonyExpectNullWhenUserHasNoColony(): void
    {
        $this->ship->shouldReceive('getUser->getColonies')
            ->withNoArgs()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->searchClosestUsableColony(
            $this->ship,
            42
        );

        $this->assertNull($result);
    }

    public function testSearchClosestUsableColonyExpectClosestColony(): void
    {
        $colonyNotEnoughSpace = $this->mock(Colony::class);
        $colonyFarAway = $this->mock(Colony::class);
        $colonyClosest = $this->mock(Colony::class);

        $populationCalculatorNotEnoughSpace = $this->mock(ColonyPopulationCalculatorInterface::class);
        $populationFarAway = $this->mock(ColonyPopulationCalculatorInterface::class);
        $populationClosest = $this->mock(ColonyPopulationCalculatorInterface::class);

        $this->ship->shouldReceive('getUser->getColonies')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$colonyNotEnoughSpace, $colonyFarAway, $colonyClosest]));
        $this->ship->shouldReceive('getSystem')
            ->withNoArgs()
            ->andReturn(null);

        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($colonyNotEnoughSpace)
            ->andReturn($populationCalculatorNotEnoughSpace);
        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($colonyFarAway)
            ->andReturn($populationFarAway);
        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($colonyClosest)
            ->andReturn($populationClosest);

        $populationCalculatorNotEnoughSpace->shouldReceive('getCrewLimit')
            ->withNoArgs()
            ->andReturn(43);
        $populationFarAway->shouldReceive('getCrewLimit')
            ->withNoArgs()
            ->andReturn(42);
        $populationClosest->shouldReceive('getCrewLimit')
            ->withNoArgs()
            ->andReturn(42);

        $colonyNotEnoughSpace->shouldReceive('getCrewAssignmentAmount')
            ->withNoArgs()
            ->andReturn(2);
        $colonyFarAway->shouldReceive('getCrewAssignmentAmount')
            ->withNoArgs()
            ->andReturn(0);
        $colonyClosest->shouldReceive('getCrewAssignmentAmount')
            ->withNoArgs()
            ->andReturn(0);

        $this->distanceCalculation->shouldReceive('spacecraftToColonyDistance')
            ->with($this->ship, $colonyFarAway)
            ->andReturn(1000);
        $this->distanceCalculation->shouldReceive('spacecraftToColonyDistance')
            ->with($this->ship, $colonyClosest)
            ->andReturn(999);

        $result = $this->subject->searchClosestUsableColony(
            $this->ship,
            42
        );

        $this->assertNotNull($result);
        $this->assertEquals(1000, $result[0]);
        $this->assertEquals($colonyClosest, $result[1]);
    }
}
