<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Override;
use Mockery\MockInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class FightLibTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemManagerInterface */
    private $shipSystemManager;
    /** @var MockInterface|CancelRepairInterface */
    private $cancelRepair;
    /** @var MockInterface|AlertLevelBasedReactionInterface */
    private $alertLevelBasedReaction;
    /** @var MockInterface|InformationFactoryInterface */
    private $informationFactory;

    /** @var MockInterface|ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $ship;

    private FightLibInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->alertLevelBasedReaction = $this->mock(AlertLevelBasedReactionInterface::class);
        $this->informationFactory = $this->mock(InformationFactoryInterface::class);

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
            $this->alertLevelBasedReaction,
            $this->informationFactory
        );
    }


    public function testReadyExpectNoActionsWhenDestroyed(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->ready($this->wrapper, $informations);
    }

    public function testReadyExpectNoActionsWhenEscapePod(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->ready($this->wrapper, $informations);
    }

    public function testReadyExpectNoActionsWhenNoBuildplan(): void
    {
        $informations = $this->mock(InformationInterface::class);

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

        $this->subject->ready($this->wrapper, $informations);
    }

    public function testReadyExpectNoActionsWhenNotEnoughCrew(): void
    {
        $informations = $this->mock(InformationInterface::class);

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

        $this->subject->ready($this->wrapper, $informations);
    }

    public function testReadyExpectSuccessWhenNoErrors(): void
    {
        $informations = $this->mock(InformationInterface::class);
        $informationWrapper = $this->mock(InformationWrapper::class);

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

        $this->informationFactory->shouldReceive('createInformationWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($informationWrapper);

        $this->alertLevelBasedReaction->shouldReceive('react')
            ->with($this->wrapper, $informationWrapper)
            ->once();

        $informations->shouldReceive('addInformationf')
            ->with('Aktionen der %s', 'shipname')
            ->once();
        $informationWrapper->shouldReceive('addInformation')
            ->with('- Das Schiff hat abgedockt')
            ->once();
        $informationWrapper->shouldReceive('dumpTo')
            ->with($informations)
            ->once();
        $informationWrapper->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('shipname');

        $this->subject->ready($this->wrapper, $informations);
    }

    public function testReadyExpectSuccessWhenErrors(): void
    {
        $informationWrapper = $this->mock(InformationWrapper::class);
        $informations = $this->mock(InformationInterface::class);

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

        $this->informationFactory->shouldReceive('createInformationWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($informationWrapper);

        $this->alertLevelBasedReaction->shouldReceive('react')
            ->with($this->wrapper, $informationWrapper)
            ->once();

        $informationWrapper->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->ready($this->wrapper, $informations);
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

    public static function provideGetAttackersAndDefendersData(): array
    {
        return [
            [ShipWrapperInterface::class, 1, 1, false],
            [ShipWrapperInterface::class, 2, 1, true],
            [FleetWrapperInterface::class, 1, 2, true],
        ];
    }

    /**
     * @dataProvider provideGetAttackersAndDefendersData
     */
    public function testGetAttackersAndDefenders(
        string $className,
        int $attackersCount,
        int $defendersCount,
        bool $expectedIsFleet
    ): void {
        $wrapper = $this->mock($className);
        $factory = $this->mock(BattlePartyFactoryInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $attackingParty = $this->mock(AttackingBattleParty::class);
        $attackedParty = $this->mock(AttackedBattleParty::class);

        $factory->shouldReceive('createAttackingBattleParty')
            ->with($wrapper)
            ->once()
            ->andReturn($attackingParty);
        $factory->shouldReceive('createAttackedBattleParty')
            ->with($targetWrapper)
            ->once()
            ->andReturn($attackedParty);

        $attackingParty->shouldReceive('count')
            ->withNoArgs()
            ->once()
            ->andReturn($attackersCount);
        $attackedParty->shouldReceive('count')
            ->withNoArgs()
            ->once()
            ->andReturn($defendersCount);

        [
            $attackers,
            $defenders,
            $isFleetFight
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper, $factory);

        $this->assertEquals($attackingParty, $attackers);
        $this->assertEquals($attackedParty, $defenders);
        $this->assertTrue($isFleetFight === $expectedIsFleet);
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
