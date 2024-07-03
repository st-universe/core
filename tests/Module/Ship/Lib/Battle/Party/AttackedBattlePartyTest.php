<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AttackedBattlePartyTest extends StuTestCase
{
    /** @var MockInterface|ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;

    /** @var MockInterface|UserInterface */
    private UserInterface $user;

    private BattlePartyInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->user = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('get->getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->user);
        $this->wrapper->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(456);
        $this->wrapper->shouldReceive('get->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $this->wrapper->shouldReceive('get->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $this->wrapper->shouldReceive('get->isBase')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $this->subject = new AttackedBattleParty(
            $this->wrapper
        );
    }

    public function testGetActiveMembersExpectSingle(): void
    {
        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([456 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectSingleWhenDockedToNpc(): void
    {
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);
        $dockedToShip = $this->mock(ShipInterface::class);
        $dockedToUser = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToShip);
        $dockedToShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(true);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([456 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectSingleWhenDockedOffline(): void
    {
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);
        $dockedToShip = $this->mock(ShipInterface::class);
        $dockedToUser = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToShip);
        $dockedToShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $this->wrapper->shouldReceive('canFire')
            ->withNoArgs()
            ->andReturn(true);
        $dockedToWrapper->shouldReceive('canFire')
            ->withNoArgs()
            ->andReturn(false);

        $members = $this->subject->getActiveMembers(true);

        $this->assertEquals([456 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectSingleAndOnlineDocked(): void
    {
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);
        $dockedToShip = $this->mock(ShipInterface::class);
        $dockedToUser = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToShip);
        $dockedToShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $this->wrapper->shouldReceive('canFire')
            ->withNoArgs()
            ->andReturn(true);
        $dockedToWrapper->shouldReceive('canFire')
            ->withNoArgs()
            ->andReturn(true);

        $members = $this->subject->getActiveMembers(true);

        $this->assertEquals([
            456 => $this->wrapper,
            789 => $dockedToWrapper
        ], $members->toArray());
    }

    public function testGetActiveMembersExpectPartialCloakedFleet(): void
    {
        $cloakedWrapper = $this->mock(ShipWrapperInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $cloakedWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                45 => $this->wrapper,
                67 => $cloakedWrapper
            ]));

        $this->wrapper->shouldReceive('get->getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $cloakedWrapper->shouldReceive('get->getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([45 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectFleetAndOnlineDocked(): void
    {
        $secondWrapper = $this->mock(ShipWrapperInterface::class);
        $secondShip = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);
        $dockedToShip = $this->mock(ShipInterface::class);
        $dockedToUser = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $secondWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                456 => $this->wrapper,
                678 => $secondWrapper
            ]));

        $this->wrapper->shouldReceive('get->getCloakState')
            ->withNoArgs()
            ->andReturn(false);

        $secondWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($secondShip);
        $secondShip->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);


        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToShip);
        $dockedToShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToShip->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToShip->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToShip->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([
            456 => $this->wrapper,
            678 => $secondWrapper,
            789 => $dockedToWrapper
        ], $members->toArray());
    }

    public function testGetActiveMembersExpectCloakedFleet(): void
    {
        $secondWrapper = $this->mock(ShipWrapperInterface::class);
        $secondShip = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $secondWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                456 => $this->wrapper,
                678 => $secondWrapper
            ]));

        $this->wrapper->shouldReceive('get->getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        $secondWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($secondShip);
        $secondShip->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(true);
        $secondShip->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([
            456 => $this->wrapper,
            678 => $secondWrapper
        ], $members->toArray());
    }
}
