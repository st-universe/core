<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMovementInformationAdderInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMover;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class ShipMoverTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spaceRepository;
    /** @var MockInterface&ShipMovementInformationAdderInterface */
    private $shipMovementInformationAdder;
    /** @var MockInterface&PreFlightConditionsCheckInterface */
    private $preFlightConditionsCheck;
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
        $this->shipMovementInformationAdder = $this->mock(ShipMovementInformationAdderInterface::class);
        $this->preFlightConditionsCheck = $this->mock(PreFlightConditionsCheckInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->subject = new ShipMover(
            $this->spaceRepository,
            $this->shipMovementInformationAdder,
            $this->preFlightConditionsCheck,
            $this->leaveFleet,
            $this->alertReactionFacade,
            $this->messageFactory
        );
    }

    public function testCheckAndMove(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map = $this->mock(MapInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);
        $failureMessage = $this->mock(MessageInterface::class);

        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(12345);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->twice()
            ->andReturn(null);

        $map->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

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

        $conditionCheckResult->shouldReceive('isFlightPossible')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $conditionCheckResult->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn(['FAILURE']);


        $this->preFlightConditionsCheck->shouldReceive('checkPreconditions')
            ->with($wrapper, [12345 => $wrapper], $flightRoute, false)
            ->once()
            ->andReturn($conditionCheckResult);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);
        $messageCollection->shouldReceive('addInformation')
            ->with('Der Weiterflug wurde aus folgenden Gründen abgebrochen:')
            ->once();
        $messageCollection->shouldReceive('add')
            ->with($failureMessage)
            ->once();
        $this->messageFactory->shouldReceive('createMessage')
            ->with(UserEnum::USER_NOONE, null, ['FAILURE'])
            ->once()
            ->andReturn($failureMessage);

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }

    public function testCheckAndMoveExpectLossOfEmptyShipIfNotFixed(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $lostShip = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $lostWrapper = $this->mock(ShipWrapperInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map1 = $this->mock(MapInterface::class);
        $map2 = $this->mock(MapInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);
        $lostMessage = $this->mock(MessageInterface::class);
        $emptyMessage = $this->mock(MessageInterface::class);

        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
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
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
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

        $flightRoute->shouldReceive('isDestinationArrived')
            ->withNoArgs()
            ->times(3)
            ->andReturn(false, false, true);
        $flightRoute->shouldReceive('getNextWaypoint')
            ->withNoArgs()
            ->twice()
            ->andReturn($map1, $map2);
        $flightRoute->shouldReceive('enterNextWaypoint')
            ->with(Mockery::on(fn(ArrayCollection $coll) => $coll->toArray() === [12345 => $wrapper]), $messageCollection)
            ->twice();
        $flightRoute->shouldReceive('getRouteMode')
            ->withNoArgs()
            ->once()
            ->andReturn(RouteModeEnum::FLIGHT);

        $conditionCheckResult->shouldReceive('isFlightPossible')
            ->withNoArgs()
            ->twice()
            ->andReturn(true);
        $conditionCheckResult->shouldReceive('getBlockedIds')
            ->withNoArgs()
            ->twice()
            ->andReturn([424242], []);
        $conditionCheckResult->shouldReceive('getInformations')
            ->withNoArgs()
            ->twice()
            ->andReturn(['LOST 424242'], []);

        $this->preFlightConditionsCheck->shouldReceive('checkPreconditions')
            ->with($wrapper, [12345 => $wrapper, 424242 => $lostWrapper], $flightRoute, false)
            ->once()
            ->andReturn($conditionCheckResult);
        $this->preFlightConditionsCheck->shouldReceive('checkPreconditions')
            ->with($wrapper, [12345 => $wrapper], $flightRoute, false)
            ->once()
            ->andReturn($conditionCheckResult);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);
        $messageCollection->shouldReceive('add')
            ->with($lostMessage)
            ->once();
        $messageCollection->shouldReceive('add')
            ->with($emptyMessage)
            ->once();
        $this->messageFactory->shouldReceive('createMessage')
            ->with(UserEnum::USER_NOONE, null, ['LOST 424242'])
            ->once()
            ->andReturn($lostMessage);
        $this->messageFactory->shouldReceive('createMessage')
            ->with(UserEnum::USER_NOONE, null, [])
            ->once()
            ->andReturn($emptyMessage);

        $this->alertReactionFacade->shouldReceive('doItAll')
            ->twice();

        $this->spaceRepository->shouldReceive('save')
            ->with($ship)
            ->once();
        $this->spaceRepository->shouldReceive('save')
            ->with($lostShip)
            ->once();

        $this->shipMovementInformationAdder->shouldReceive('reachedDestination')
            ->with($ship, true, RouteModeEnum::FLIGHT, $messageCollection)
            ->once();

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }

    public function testCheckAndMoveExpectNoAlertCheckIfDestroyedOnEntrance(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map = $this->mock(MapInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);
        $messageCollection = $this->mock(MessageCollectionInterface::class);
        $emptyMessage = $this->mock(MessageInterface::class);

        $shipId = 12345;

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($shipId);
        $ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("SHIP");
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getTractoredShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->times(5)
            ->andReturn(false, true, true, true, true);
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
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $map->shouldReceive('getFieldType->getPassable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

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
                Mockery::on(fn(ArrayCollection $coll) => $coll->toArray() === [$shipId => $wrapper]),
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

        $conditionCheckResult->shouldReceive('isFlightPossible')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $conditionCheckResult->shouldReceive('getBlockedIds')
            ->withNoArgs()
            ->once()
            ->andReturn([]);
        $conditionCheckResult->shouldReceive('getInformations')
            ->withNoArgs()
            ->once()
            ->andReturn([]);

        $this->preFlightConditionsCheck->shouldReceive('checkPreconditions')
            ->with($wrapper, [$shipId => $wrapper], $flightRoute, false)
            ->once()
            ->andReturn($conditionCheckResult);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messageCollection);
        $messageCollection->shouldReceive('add')
            ->with($emptyMessage)
            ->once();
        $messageCollection->shouldReceive('addInformation')
            ->with('Es wurden alle Schiffe zerstört')
            ->once();
        $this->messageFactory->shouldReceive('createMessage')
            ->with(UserEnum::USER_NOONE, null, [])
            ->once()
            ->andReturn($emptyMessage);

        $this->shipMovementInformationAdder->shouldReceive('reachedDestinationDestroyed')
            ->with($ship, 'SHIP', false, RouteModeEnum::FLIGHT, $messageCollection)
            ->once();

        $this->subject->checkAndMove($wrapper, $flightRoute);
    }
}
