<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AttackedBattlePartyTest extends StuTestCase
{
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&StuRandom $stuRandom;

    private MockInterface&User $user;

    private BattlePartyInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        //other
        $this->user = $this->mock(User::class);

        $this->wrapper->shouldReceive('get->getUser')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->user);
        $this->wrapper->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(456);
        $this->wrapper->shouldReceive('get->getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $this->wrapper->shouldReceive('get->getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $this->wrapper->shouldReceive('get->isStation')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $this->subject = new AttackedBattleParty(
            $this->wrapper, $this->stuRandom
        );
    }

    public function testGetActiveMembersExpectSingle(): void
    {
        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([456 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectSingleWhenDockedToNpc(): void
    {
        $dockedToWrapper = $this->mock(StationWrapperInterface::class);
        $dockedToStation = $this->mock(Station::class);
        $dockedToUser = $this->mock(User::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToStation);
        $dockedToStation->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToStation->shouldReceive('getUser')
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
        $dockedToWrapper = $this->mock(StationWrapperInterface::class);
        $dockedToStation = $this->mock(Station::class);
        $dockedToUser = $this->mock(User::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToStation);
        $dockedToStation->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToStation->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDisabled')
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
        $dockedToWrapper = $this->mock(StationWrapperInterface::class);
        $dockedToStation = $this->mock(Station::class);
        $dockedToUser = $this->mock(User::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToStation);
        $dockedToStation->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToStation->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDisabled')
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
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $cloakedWrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                45 => $this->wrapper,
                67 => $cloakedWrapper
            ]));

        $this->wrapper->shouldReceive('get->isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $cloakedWrapper->shouldReceive('get->isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([45 => $this->wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectFleetAndOnlineDocked(): void
    {
        $secondWrapper = $this->mock(ShipWrapperInterface::class);
        $secondShip = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $dockedToWrapper = $this->mock(StationWrapperInterface::class);
        $dockedToStation = $this->mock(Station::class);
        $dockedToUser = $this->mock(User::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $secondWrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                456 => $this->wrapper,
                678 => $secondWrapper
            ]));

        $this->wrapper->shouldReceive('get->isCloaked')
            ->withNoArgs()
            ->andReturn(false);

        $secondWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($secondShip);
        $secondShip->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);


        $dockedToWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($dockedToStation);
        $dockedToStation->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(789);
        $dockedToStation->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($dockedToUser);
        $dockedToUser->shouldReceive('isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToStation->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $dockedToStation->shouldReceive('isCloaked')
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
        $secondShip = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $secondWrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                456 => $this->wrapper,
                678 => $secondWrapper
            ]));

        $this->wrapper->shouldReceive('get->isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $secondWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($secondShip);
        $secondShip->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);
        $secondShip->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $secondShip->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $members = $this->subject->getActiveMembers();

        $this->assertEquals([
            456 => $this->wrapper,
            678 => $secondWrapper
        ], $members->toArray());
    }
}
