<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class IncomingBattlePartyTest extends StuTestCase
{
    public function testGetActiveMembersExpectEmptyWhenCloaked(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertTrue($members->isEmpty());
    }

    public function testGetActiveMembersExpectEmptyWhenWarped(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertTrue($members->isEmpty());
    }

    public function testGetActiveMembersExpectSingleWhenUncloakedAndUnwarped(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('getDockedToStationWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new IncomingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectUncloakedAndUnwarpedFleetShips(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $wrapperCloaked = $this->mock(ShipWrapperInterface::class);
        $wrapperWarped = $this->mock(ShipWrapperInterface::class);
        $shipCloaked = $this->mock(Ship::class);
        $shipWarped = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapperCloaked->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipCloaked);
        $wrapperWarped->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipWarped);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                12 => $wrapper,
                34 => $wrapperCloaked,
                56 => $wrapperWarped
            ]));

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $shipCloaked->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $shipWarped->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $shipWarped->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new IncomingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper
        ], $members->toArray());
    }
}
