<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ColonyDefendingBattlePartyTest extends StuTestCase
{
    public function testGetActiveMembersExpectSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

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
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);

        $subject = new ColonyDefendingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectUncloakedFleetShips(): void
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

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);
        $ship2->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ColonyDefendingBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper
        ], $members->toArray());
    }

    public function testGetAttackCause(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $subject = new ColonyDefendingBattleParty($wrapper);
        $result = $subject->getAttackCause();

        $this->assertEquals(SpacecraftAttackCauseEnum::COLONY_DEFENSE, $result);
    }

    public function testGetAlertDescription(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $subject = new ColonyDefendingBattleParty($wrapper);

        $result = $subject->getAlertDescription();

        $this->assertEquals('[b][color=orange]Kolonie-Verteidigung[/color][/b]', $result);
    }
}
