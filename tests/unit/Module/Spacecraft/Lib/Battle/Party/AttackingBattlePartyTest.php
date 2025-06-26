<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AttackingBattlePartyTest extends StuTestCase
{
    public function testGetActiveMembersExpectSingle(): void
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

        $subject = new AttackingBattleParty($wrapper, true);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
        $this->assertTrue($subject->isAttackingShieldsOnly());
    }

    public function testGetActiveMembersExpectSingleWhenNotFleetLeader(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
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

        $subject = new AttackingBattleParty($wrapper, false);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
        $this->assertFalse($subject->isAttackingShieldsOnly());
    }

    public function testGetActiveMembersExpectFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship2 = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(User::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $condition2 = $this->mock(SpacecraftCondition::class);

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

        $ship->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($condition);
        $ship2->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($condition2);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                12 => $wrapper,
                34 => $wrapper2
            ]));

        $condition->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $condition->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $condition2->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $condition2->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new AttackingBattleParty($wrapper, true);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $members->toArray());
    }

    public function testGetActiveMembersExpectRealFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship2 = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(User::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $condition2 = $this->mock(SpacecraftCondition::class);

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

        $ship->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($condition);
        $ship2->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($condition2);

        $fleetWrapper->shouldReceive('getLeadWrapper')
            ->withNoArgs()
            ->andReturn($wrapper);
        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                12 => $wrapper,
                34 => $wrapper2
            ]));

        $condition->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $condition->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $condition2->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $condition2->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new AttackingBattleParty($fleetWrapper, false);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $members->toArray());
    }
}
