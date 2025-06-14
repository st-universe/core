<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AlertStateBattlePartyTest extends StuTestCase
{
    public function testGetActiveMembersExpectSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
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

        $subject = new AlertStateBattleParty($wrapper);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapper], $members->toArray());
        $this->assertTrue($subject->isStation());
    }

    public function testGetActiveMembersExpectOnlyUncloakedAndUnwarpedWithSufficientAlertState(): void
    {
        $wrapperOk = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapperCloaked = $this->mock(ShipWrapperInterface::class);
        $shipCloaked = $this->mock(ShipInterface::class);
        $wrapperWarped = $this->mock(ShipWrapperInterface::class);
        $shipWarped = $this->mock(ShipInterface::class);
        $wrapperLowAlert = $this->mock(ShipWrapperInterface::class);
        $shipLowerAlert = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $user = $this->mock(UserInterface::class);

        $wrapperOk->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $wrapperOk->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapperOk->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('getCondition->isDisabled')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn(new ArrayCollection([
                123 => $wrapperOk,
                2 => $wrapperCloaked,
                3 => $wrapperWarped,
                4 => $wrapperLowAlert
            ]));

        $wrapperCloaked->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipCloaked);
        $shipCloaked->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $wrapperWarped->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipWarped);
        $shipWarped->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $shipWarped->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

        $wrapperLowAlert->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($shipLowerAlert);
        $wrapperLowAlert->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $shipLowerAlert->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(false);
        $shipLowerAlert->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);


        $subject = new AlertStateBattleParty($wrapperOk);

        $members = $subject->getActiveMembers();

        $this->assertEquals([123 => $wrapperOk], $members->toArray());
        $this->assertFalse($subject->isStation());
    }
}
