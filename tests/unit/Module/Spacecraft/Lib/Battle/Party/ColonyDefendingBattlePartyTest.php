<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ColonyDefendingBattlePartyTest extends StuTestCase
{
    private MockInterface&StuRandom $stuRandom;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->stuRandom = $this->mock(StuRandom::class);
    }

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

        $subject = new ColonyDefendingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
    }

    public function testGetActiveMembersExpectUncloakedFleetShips(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship2 = $this->mock(Ship::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(User::class);

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
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);
        $ship2->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->andReturn(false);
        $ship2->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ColonyDefendingBattleParty($wrapper, $this->stuRandom);

        $members = $subject->getActiveMembers();

        $this->assertEquals([
            12 => $wrapper
        ], $members->toArray());
    }

    public function testGetAttackCause(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $subject = new ColonyDefendingBattleParty($wrapper, $this->stuRandom);
        $result = $subject->getAttackCause();

        $this->assertEquals(SpacecraftAttackCauseEnum::COLONY_DEFENSE, $result);
    }

    public function testGetAlertDescription(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);
        $user = $this->mock(User::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);

        $subject = new ColonyDefendingBattleParty($wrapper, $this->stuRandom);

        $result = $subject->getAlertDescription();

        $this->assertEquals('[b][color=orange]Kolonie-Verteidigung[/color][/b]', $result);
    }
}
