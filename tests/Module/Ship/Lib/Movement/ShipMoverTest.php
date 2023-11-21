<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Ship\Lib\Movement\ShipMovementInformationAdderInterface;
use Stu\Module\Ship\Lib\Movement\ShipMover;
use Stu\Module\Ship\Lib\Movement\ShipMoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;


class ShipMoverTest extends StuTestCase
{
    /** @var MockInterface&ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface&ShipMovementInformationAdderInterface */
    private MockInterface $shipMovementInformationAdder;

    /** @var MockInterface&PreFlightConditionsCheckInterface */
    private MockInterface $preFlightConditionsCheck;

    /** @var MockInterface&LeaveFleetInterface */
    private MockInterface $leaveFleet;

    /** @var MockInterface&AlertRedHelperInterface */
    private MockInterface $alertRedHelper;

    private ShipMoverInterface $subject;

    protected function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipMovementInformationAdder = $this->mock(ShipMovementInformationAdderInterface::class);
        $this->preFlightConditionsCheck = $this->mock(PreFlightConditionsCheckInterface::class);
        $this->leaveFleet = $this->mock(LeaveFleetInterface::class);
        $this->alertRedHelper = $this->mock(AlertRedHelperInterface::class);

        $this->subject = new ShipMover(
            $this->shipRepository,
            $this->shipMovementInformationAdder,
            $this->preFlightConditionsCheck,
            $this->leaveFleet,
            $this->alertRedHelper
        );
    }

    public function testCheckAndMove(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $flightRoute = $this->mock(FlightRouteInterface::class);
        $map = $this->mock(MapInterface::class);
        $conditionCheckResult = $this->mock(ConditionCheckResult::class);

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

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
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
            ->with($wrapper, [$wrapper], $flightRoute, false)
            ->once()
            ->andReturn($conditionCheckResult);


        $this->subject->checkAndMove($wrapper, $flightRoute);
    }
}
