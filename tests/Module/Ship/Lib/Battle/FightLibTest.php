<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
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
        $target = $this->mock(ShipInterface::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $result = $this->subject->canAttackTarget($ship, $target);

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
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

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
        $target->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
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

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);
        $target->shouldReceive('isTrumfield')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getWarpState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getFleetId')
            ->withNoArgs()
            ->andReturn(43);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }
}
