<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class FightLibTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemManagerInterface */
    private ShipSystemManagerInterface $shipSystemManager;

    /** @var MockInterface|CancelRepairInterface */
    private CancelRepairInterface $cancelRepair;

    /** @var MockInterface|AlertLevelBasedReactionInterface */
    private AlertLevelBasedReactionInterface $alertLevelBasedReaction;

    /** @var MockInterface|ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $ship;

    private FightLibInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->alertLevelBasedReaction = $this->mock(AlertLevelBasedReactionInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->ship = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new FightLib(
            $this->shipSystemManager,
            $this->cancelRepair,
            $this->alertLevelBasedReaction
        );
    }


    public function testReadyExpectNoActionsWhenDestroyed(): void
    {
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testReadyExpectNoActionsWhenEscapePod(): void
    {
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testReadyExpectNoActionsWhenNoBuildplan(): void
    {
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testReadyExpectNoActionsWhenNotEnoughCrew(): void
    {
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipBuildplanInterface::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testReadyExpectSuccessWhenNoErrors(): void
    {
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipBuildplanInterface::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipInterface::class));
        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once();
        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK)
            ->once();

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->alertLevelBasedReaction->shouldReceive('react')
            ->with($this->wrapper)
            ->once()
            ->andReturn(new InformationWrapper(['test']));

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('shipname');

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals(['Aktionen der shipname', '- Das Schiff hat abgedockt', 'test'], $result->getInformations());
    }

    public function testReadyExpectSuccessWhenErrors(): void
    {
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ShipBuildplanInterface::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK)
            ->once()
            ->andThrow(new SystemNotFoundException());

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->alertLevelBasedReaction->shouldReceive('react')
            ->with($this->wrapper)
            ->once()
            ->andReturn(new InformationWrapper());

        $result = $this->subject->ready($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testFilterInactiveShips(): void
    {
        $wrapperDestroyed = $this->mock(ShipWrapperInterface::class);
        $wrapperDisabled = $this->mock(ShipWrapperInterface::class);
        $wrapperAllRight = $this->mock(ShipWrapperInterface::class);

        $shipDestroyed = $this->mock(ShipInterface::class);
        $shipDisabled = $this->mock(ShipInterface::class);
        $shipAllRight = $this->mock(ShipInterface::class);

        $wrapperDestroyed->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($shipDestroyed);
        $wrapperDisabled->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($shipDisabled);
        $wrapperAllRight->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($shipAllRight);

        $shipDestroyed->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $shipDisabled->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $shipDisabled->shouldReceive('isDisabled')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $shipAllRight->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $shipAllRight->shouldReceive('isDisabled')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $wrappers = [$wrapperAllRight, $wrapperDestroyed, $wrapperDisabled];

        $result = $this->subject->filterInactiveShips($wrappers);

        $this->assertEquals([$wrapperAllRight], $result);
    }

    public function testCanFireExpectFalseWhenNbsOffline(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canFire($this->wrapper);

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenWeaponsOffline(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canFire($this->wrapper);

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenNoEpsInstalled(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->canFire($this->wrapper);

        $this->assertFalse($result);
    }

    public function testCanFireExpectTrueWhenEverythingIsFine(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystemData);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->canFire($this->wrapper);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectFalseWhenNoActiveWeapon(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingSelf(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $ship);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingTrumfield(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingCloakedTarget(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target, true);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenTractoredAndAttackingOtherTarget(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $tractoringShip = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn($tractoringShip);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $tractoringShip->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingTractoringShip(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingTractoringShipWithoutWeaponCheck(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canAttackTarget($ship, $target, false, false);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingWarpedTarget(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingOwnCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingOwnFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingSingleShip(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(null);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackerIsSingleShip(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingOtherFleet(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(43);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectTrueWithoutWarpCheck(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringShip')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(42);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(43);

        $result = $this->subject->canAttackTarget($ship, $target, false, true, false);

        $this->assertTrue($result);
    }

    public function testGetAttackersAndDefendersExpectSingleVsSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([456 => $targetWrapper], $defenders);
        $this->assertFalse($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsSingleWhenDockedToNpc(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        $dockedToWrapper->shouldReceive('get->getUser->isNpc')
            ->withNoArgs()
            ->andReturn(true);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([456 => $targetWrapper], $defenders);
        $this->assertFalse($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsSingleWhenDockedOffline(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        $dockedToWrapper->shouldReceive('get->getUser->isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToWrapper->shouldReceive('get->hasActiveWeapon')
            ->withNoArgs()
            ->andReturn(false);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([456 => $targetWrapper], $defenders);
        $this->assertFalse($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsSingleAndOnlineDocked(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        $dockedToWrapper->shouldReceive('get->getUser->isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToWrapper->shouldReceive('get->hasActiveWeapon')
            ->withNoArgs()
            ->andReturn(true);
        $dockedToWrapper->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(789);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([
            456 => $targetWrapper,
            789 => $dockedToWrapper
        ], $defenders);
        $this->assertTrue($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectFleetVsSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($fleetWrapper);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(true);
        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn([
                12 => $wrapper,
                34 => $wrapper2
            ]);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $attackers);
        $this->assertEquals([456 => $targetWrapper], $defenders);
        $this->assertTrue($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectRealFleetVsSingle(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);

        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn([
                12 => $wrapper,
                34 => $wrapper2
            ]);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($fleetWrapper, $targetWrapper);

        $this->assertEquals([
            12 => $wrapper,
            34 => $wrapper2
        ], $attackers);
        $this->assertEquals([456 => $targetWrapper], $defenders);
        $this->assertTrue($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsPartialCloakedFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $targetWrapperCloaked = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $targetCloaked = $this->mock(ShipInterface::class);
        $targetFleetWrapper = $this->mock(FleetWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($targetFleetWrapper);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetFleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn([
                45 => $targetWrapper,
                67 => $targetWrapperCloaked
            ]);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapperCloaked->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($targetCloaked);

        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $targetCloaked->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([45 => $targetWrapper], $defenders);
        $this->assertFalse($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsFleetAndOnlineDocked(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $targetWrapperCloaked = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $targetCloaked = $this->mock(ShipInterface::class);
        $targetFleetWrapper = $this->mock(FleetWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($targetFleetWrapper);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $targetFleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn([
                45 => $targetWrapper,
                67 => $targetWrapperCloaked
            ]);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapperCloaked->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($targetCloaked);

        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $targetCloaked->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(true);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([45 => $targetWrapper], $defenders);
        $this->assertFalse($isFleetFight);
    }

    public function testGetAttackersAndDefendersExpectSingleVsCloakedFleet(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(ShipInterface::class);

        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $targetWrapper2 = $this->mock(ShipWrapperInterface::class);
        $target = $this->mock(ShipInterface::class);
        $target2 = $this->mock(ShipInterface::class);
        $targetFleetWrapper = $this->mock(FleetWrapperInterface::class);

        $dockedToWrapper = $this->mock(ShipWrapperInterface::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);
        $wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('isFleetLeader')
            ->withNoArgs()
            ->andReturn(false);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->andReturn($targetFleetWrapper);
        $targetWrapper->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);
        $targetWrapper2->shouldReceive('getDockedToShipWrapper')
            ->withNoArgs()
            ->andReturn($dockedToWrapper);
        $targetFleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->andReturn([
                45 => $targetWrapper,
                67 => $targetWrapper2
            ]);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);
        $targetWrapper2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target2);

        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target2->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);

        $dockedToWrapper->shouldReceive('get->getUser->isNpc')
            ->withNoArgs()
            ->andReturn(false);
        $dockedToWrapper->shouldReceive('get->hasActiveWeapon')
            ->withNoArgs()
            ->andReturn(true);
        $dockedToWrapper->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(789);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper);

        $this->assertEquals([123 => $wrapper], $attackers);
        $this->assertEquals([
            45 => $targetWrapper,
            67 => $targetWrapper2,
            789 => $dockedToWrapper
        ], $defenders);
        $this->assertTrue($isFleetFight);
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenNoWeb(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenWebUnfinished(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $web = $this->mock(TholianWebInterface::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectFalseWhenTargetInSameFinishedWeb(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $web = $this->mock(TholianWebInterface::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertFalse($result);
    }

    public function testisTargetOutsideFinishedTholianWebExpectTrueWhenTargetOutsideFinishedWeb(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $target = $this->mock(ShipInterface::class);
        $web = $this->mock(TholianWebInterface::class);

        $ship->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn($web);
        $web->shouldReceive('isFinished')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $target->shouldReceive('getHoldingWeb')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $result = $this->subject->isTargetOutsideFinishedTholianWeb($ship, $target);

        $this->assertTrue($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenNpc(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID - 1);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenBase(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID);

        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenTrumfield(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID);

        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenCloaked(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID);

        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenShieldsOn(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID);

        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenWarped(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserEnum::USER_FIRST_ID);

        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('getShieldState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertTrue($result);
    }
}
