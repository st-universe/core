<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ClosestLocationsTest extends StuTestCase
{
    /** @var MockInterface&DistanceCalculationInterface */
    private MockInterface $distanceCalculation;

    /** @var MockInterface&ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface&TroopTransferUtilityInterface */
    private MockInterface $troopTransferUtility;

    /** @var MockInterface&ColonyLibFactoryInterface */
    private MockInterface $colonyLibFactory;

    private ShipInterface $ship;

    private ClosestLocations $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->distanceCalculation = $this->mock(DistanceCalculationInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);

        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new ClosestLocations(
            $this->distanceCalculation,
            $this->shipRepository,
            $this->troopTransferUtility,
            $this->colonyLibFactory
        );
    }

    public function testSearchClosestUsableStationExpectNullWhenUserHasNoStations(): void
    {
        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->shipRepository->shouldReceive('getStationsByUser')
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
        $stationUnmanned = $this->mock(ShipInterface::class);
        $stationFarAway = $this->mock(ShipInterface::class);
        $stationClosest = $this->mock(ShipInterface::class);
        $stationNotEnoughSpace = $this->mock(ShipInterface::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(5);

        $this->shipRepository->shouldReceive('getStationsByUser')
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

        $this->distanceCalculation->shouldReceive('shipToShipDistance')
            ->with($this->ship, $stationFarAway)
            ->andReturn(1000);
        $this->distanceCalculation->shouldReceive('shipToShipDistance')
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
        $colonyNotEnoughSpace = $this->mock(ColonyInterface::class);
        $colonyFarAway = $this->mock(ColonyInterface::class);
        $colonyClosest = $this->mock(ColonyInterface::class);

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

        $this->distanceCalculation->shouldReceive('shipToColonyDistance')
            ->with($this->ship, $colonyFarAway)
            ->andReturn(1000);
        $this->distanceCalculation->shouldReceive('shipToColonyDistance')
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
