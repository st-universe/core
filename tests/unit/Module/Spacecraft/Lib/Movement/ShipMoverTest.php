<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMovementInformationAdderInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMover;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompanyFactory;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class ShipMoverTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spaceRepository;
    /** @var MockInterface&FlightCompanyFactory */
    private $flightCompanyFactory;
    /** @var MockInterface&ShipMovementInformationAdderInterface */
    private $shipMovementInformationAdder;
    /** @var MockInterface&LeaveFleetInterface */
    private $leaveFleet;
    /** @var MockInterface&AlertReactionFacadeInterface */
    private $alertReactionFacade;
    /** @var MockInterface&MessageFactoryInterface */
    private $messageFactory;

    private ShipMoverInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->spaceRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->flightCompanyFactory = $this->mock(FlightCompanyFactory::class);
        $this->shipMovementInformationAdder = $this->mock(ShipMovementInformationAdderInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->subject = new ShipMover(
            $this->spaceRepository,
            $this->flightCompanyFactory,
            $this->shipMovementInformationAdder,
            $this->leaveFleet,
            $this->alertReactionFacade,
            $this->messageFactory
        );
    }

    public function testCheckAndMoveExpectAbortionWhenFlightNotPossible(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map = $this->mock(MapInterface::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);

        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $map->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->flightCompanyFactory->shouldReceive('create')
            ->with($wrapper)
            ->once()
            ->andReturn($flightCompany);

        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$wrapper]));
        $flightCompany->shouldReceive('isFlightPossible')
            ->with($flightRoute, $messageCollection)
            ->once()
            ->andReturn(false);

        $flightRoute->shouldReceive('isDestinationArrived')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $flightRoute->shouldReceive('abortFlight')
            ->withNoArgs()
            ->once();

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }

    public function testCheckAndMoveExpectLossOfEmptyShipIfNotFixed(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $ship = $this->mock(ShipInterface::class);
        $lostShip = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $lostWrapper = $this->mock(ShipWrapperInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map1 = $this->mock(MapInterface::class);
        $map2 = $this->mock(MapInterface::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);

        $flightCompany->shouldReceive('isFleetMode')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $flightCompany->shouldReceive('getLeadWrapper')
            ->withNoArgs()
            ->andReturn($wrapper);
        $flightCompany->shouldReceive('isFlightPossible')
            ->with($flightRoute, $messageCollection)
            ->twice()
            ->andReturn(true);
        $flightCompany->shouldReceive('hasToLeaveFleet')
            ->withNoArgs()
            ->andReturn(false);
        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->times(5)
            ->andReturn(new ArrayCollection([$wrapper]));
        $flightCompany->shouldReceive('isEmpty')
            ->withNoArgs()
            ->times(4)
            ->andReturn(false);
        $flightCompany->shouldReceive('isEverybodyDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->andReturn(null);
        $lostShip->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $lostShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map2);

        $map2->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $map2->shouldReceive('getBuoys')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $lostWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($lostShip);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->twice()
            ->andReturn(null);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([12345 => $wrapper, 424242 => $lostWrapper]));
        $fleetWrapper->shouldReceive('get->isFleetFixed')
            ->withNoArgs()
            ->andReturn(false);

        $map1->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $map2->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->flightCompanyFactory->shouldReceive('create')
            ->with($wrapper)
            ->once()
            ->andReturn($flightCompany);

        $flightRoute->shouldReceive('isDestinationArrived')
            ->withNoArgs()
            ->times(3)
            ->andReturn(false, false, true);
        $flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->twice()
            ->andReturn($map1, $map2);
        $flightRoute->shouldReceive('enterNextWaypoint')
            ->with($flightCompany, $messageCollection)
            ->twice();
        $flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::FLIGHT);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);

        $this->alertReactionFacade->shouldReceive('doItAll')
            ->twice();

        $this->spaceRepository->shouldReceive('save')
            ->with($ship)
            ->once();

        $this->shipMovementInformationAdder->shouldReceive('reachedDestination')
            ->with($ship, true, RouteModeEnum::FLIGHT, $messageCollection)
            ->once();

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }

    public function testCheckAndMoveExpectNoAlertCheckIfDestroyedOnEntrance(): void
    {
        $flightCompany = $this->mock(FlightCompany::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map = $this->mock(MapInterface::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);

        $flightCompany->shouldReceive('isFleetMode')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $flightCompany->shouldReceive('getLeader')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);
        $flightCompany->shouldReceive('getLeadWrapper')
            ->withNoArgs()
            ->andReturn($wrapper);
        $flightCompany->shouldReceive('isFlightPossible')
            ->with($flightRoute, $messageCollection)
            ->once()
            ->andReturn(true);
        $flightCompany->shouldReceive('hasToLeaveFleet')
            ->withNoArgs()
            ->andReturn(false);
        $flightCompany->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->times(4)
            ->andReturn(
                new ArrayCollection([$wrapper]), //initTractoredShips
                new ArrayCollection([$wrapper]), //moveShipsByOneField
                new ArrayCollection(), //saveShips
                new ArrayCollection() //postFlightInformations
            );
        $flightCompany->shouldReceive('isEmpty')
            ->withNoArgs()
            ->twice()
            ->andReturn(true);
        $flightCompany->shouldReceive('isEverybodyDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $map->shouldReceive('getBuoys')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $map->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->flightCompanyFactory->shouldReceive('create')
            ->with($wrapper)
            ->once()
            ->andReturn($flightCompany);

        $flightRoute->shouldReceive('isDestinationArrived')
            ->withNoArgs()
            ->times(2)
            ->andReturn(false, true);
        $flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->once()
            ->andReturn($map);
        $flightRoute->shouldReceive('enterNextWaypoint')
            ->with(
                $flightCompany,
                $messageCollection
            )
            ->once();
        $flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::FLIGHT);
        $flightRoute->shouldReceive('abortFlight')
            ->withNoArgs()
            ->once();

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);

        $this->shipMovementInformationAdder->shouldReceive('reachedDestinationDestroyed')
            ->with($ship, 'SHIP', false, RouteModeEnum::FLIGHT, $messageCollection)
            ->once();

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }
}
