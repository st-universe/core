<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AlertedShipsDetectionTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private $spacecraftWrapperFactory;

    /** @var MockInterface&LocationInterface */
    private $location;

    private AlertedShipsDetectionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);

        $this->location = $this->mock(LocationInterface::class);

        $this->subject = new AlertedShipsDetection(
            $this->spacecraftWrapperFactory
        );
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenNoShipsOnLocation(): void
    {
        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenSameUser(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $user
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenNotFleetLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
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

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenAlertGreen(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenWarped(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenFleetLeader(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$ship]));

        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenSingleton(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $this->location->shouldReceive('getSpacecraftsWithoutVacation')
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
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $this->mock(UserInterface::class)
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }
}
