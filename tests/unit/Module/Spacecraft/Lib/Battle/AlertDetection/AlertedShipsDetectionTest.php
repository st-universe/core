<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AlertedShipsDetectionTest extends StuTestCase
{
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&Location $location;

    private AlertedShipsDetectionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);

        $this->location = $this->mock(Location::class);

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
            $this->mock(User::class)
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenSameUser(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);

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
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
            ->andReturn($this->mock(Fleet::class));

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $otherUser
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenWarped(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $otherUser
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenCloaked(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $otherUser
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectEmptyCollectionWhenAlertGreen(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $wrapper->shouldReceive('isUnalerted')
            ->withNoArgs()
            ->andReturn(true);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($ship)
            ->once()
            ->andReturn($wrapper);

        $result = $this->subject->getAlertedShipsOnLocation(
            $this->location,
            $otherUser
        );

        $this->assertTrue($result->isEmpty());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenFleetLeader(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
        $wrapper->shouldReceive('isUnalerted')
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
            $otherUser
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }

    public function testGetAlertedShipsOnLocationExpectWrapperWhenSingleton(): void
    {
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $otherUser = $this->mock(User::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

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
        $wrapper->shouldReceive('isUnalerted')
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
            $otherUser
        );

        $this->assertEquals(1, $result->count());
        $this->assertSame($wrapper, $result->first());
    }
}
