<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AlertedShipsDetectionTest extends StuTestCase
{
    /** @var MockInterface|ShipWrapperFactoryInterface */
    private $shipWrapperFactory;

    /** @var MockInterface|ShipInterface */
    private $incomingShip;

    private AlertedShipsDetectionInterface $subject;

    public function setUp(): void
    {
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);

        $this->incomingShip = $this->mock(ShipInterface::class);

        $this->subject = new AlertedShipsDetection(
            $this->shipWrapperFactory
        );
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenNoShipsOnLocation(): void
    {
        $currentMapField = $this->mock(MapInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenUserOnVacation(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenSameUser(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenNotFleetLeader(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn($this->mock(FleetInterface::class));

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenAlertGreen(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(true);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenWarped(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenCloaked(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenFleetLeader(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenSingleton(): void
    {
        $currentMapField = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->incomingShip->shouldReceive('getCurrentMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($currentMapField);

        $currentMapField->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);

        $this->incomingShip->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(UserInterface::class));

        $user->shouldReceive('isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->incomingShip,
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }
}
