<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AttackingBattlePartyTest extends StuTestCase
{
    public function testGetActiveMembersExpectSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('getDockedToShipWrapper')
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
        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $subject = new AttackingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectSingleWhenNotFleetLeader(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $wrapper->shouldReceive('getDockedToShipWrapper')
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
        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $subject = new AttackingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship2 = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship2);
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
                34 => $wrapper2
            ]));

        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new AttackingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $members->toArray());
    }

    public function testGetActiveMembersExpectRealFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship2 = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship2);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);

        $fleetWrapper->shouldReceive('getLeadWrapper')
            ->withNoArgs()
            ->andReturn($wrapper);
        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                12 => $wrapper,
                34 => $wrapper2
            ]));

        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new AttackingBattleParty($fleetWrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $members->toArray());
    }
}
