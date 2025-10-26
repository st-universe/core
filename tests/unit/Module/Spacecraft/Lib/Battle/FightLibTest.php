<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Ship\Retrofit\CancelRetrofitInterface;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\TrumfieldNfsItem;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

class FightLibTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&CancelRepairInterface $cancelRepair;
    private MockInterface&CancelRetrofitInterface $cancelRetrofit;
    private MockInterface&AlertLevelBasedReactionInterface $alertLevelBasedReaction;
    private MockInterface&InformationFactoryInterface $informationFactory;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private InformationWrapper $individualInformationWrapper;

    private FightLibInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->cancelRetrofit = $this->mock(CancelRetrofitInterface::class);
        $this->alertLevelBasedReaction = $this->mock(AlertLevelBasedReactionInterface::class);
        $this->informationFactory = $this->mock(InformationFactoryInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->ship = $this->mock(Ship::class);
        $this->individualInformationWrapper = new InformationWrapper();

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->informationFactory->shouldReceive('createInformationWrapper')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->individualInformationWrapper);

        $this->subject = new FightLib(
            $this->spacecraftSystemManager,
            $this->cancelRepair,
            $this->cancelRetrofit,
            $this->alertLevelBasedReaction,
            $this->informationFactory
        );
    }


    public function testReadyExpectNoActionsWhenDestroyed(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->ready($this->wrapper, true, $informations);
    }

    public function testReadyExpectNoActionsWhenEscapePod(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->ready($this->wrapper, false, $informations);
    }

    public function testReadyExpectUndockingWhenDockedAndUndockingMandatory(): void
    {
        $informations = $this->mock(InformationInterface::class);
        $station = $this->mock(Station::class);

        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn($station);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('SHIPNAME');

        $this->ship->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $informations->shouldReceive('addInformationf')
            ->with('Aktionen der %s', 'SHIPNAME')
            ->once();
        $informations->shouldReceive('addInformation')
            ->with('- Das Schiff hat abgedockt')
            ->once();

        $this->subject->ready($this->wrapper, true, $informations);
    }

    public function testReadyExpectNoActionsWhenNoBuildplan(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getRump->isEscapePods')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getCondition->isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getDockedTo')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->ready($this->wrapper, true, $informations);
    }

    public function testReadyExpectNoActionsWhenNotEnoughCrew(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(SpacecraftBuildplan::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->ready($this->wrapper, false, $informations);
    }

    public function testReadyExpectSuccessWhenNoErrors(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(SpacecraftBuildplan::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::CLOAK)
            ->once();

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($this->ship)
            ->once();

        $this->alertLevelBasedReaction->shouldReceive('react')
            ->with($this->wrapper, $this->individualInformationWrapper)
            ->once();

        $this->subject->ready($this->wrapper, false, $informations);
    }

    public function testReadyExpectSuccessWhenErrors(): void
    {
        $informations = $this->mock(InformationInterface::class);

        $this->ship->shouldReceive('getCondition->isDestroyed')
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
            ->andReturn($this->mock(SpacecraftBuildplan::class));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::CLOAK)
            ->once()
            ->andThrow(new SystemNotFoundException());

        $this->cancelRepair->shouldReceive('cancelRepair')
            ->with($this->ship)
            ->once();

        $this->cancelRetrofit->shouldReceive('cancelRetrofit')
            ->with($this->ship)
            ->once();

        $this->subject->ready($this->wrapper, false, $informations);
    }

    public function testCanAttackTargetExpectFalseWhenNoActiveWeapon(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingSelf(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $ship);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingCloakedTarget(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);


        $target->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target, true);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenTractoredAndAttackingOtherTarget(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);
        $tractoringShip = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn($tractoringShip);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);


        $tractoringShip->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(999);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingTractoringShip(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);


        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectTrueWhenAttackingTractoringShipWithoutWeaponCheck(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);


        $result = $this->subject->canAttackTarget($ship, $target, false, false);

        $this->assertTrue($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingWarpedTarget(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn(null);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingOwnCloaked(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
            ->withNoArgs()
            ->andReturn(null);
        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
            ->withNoArgs()
            ->andReturn(true);

        $result = $this->subject->canAttackTarget($ship, $target);

        $this->assertFalse($result);
    }

    public function testCanAttackTargetExpectFalseWhenAttackingOwnFleet(): void
    {
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
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

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
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
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
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

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
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
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
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

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
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
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
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

        $target->shouldReceive('isWarped')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
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
        $ship = $this->mock(Ship::class);
        $target = $this->mock(Ship::class);

        $ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $ship->shouldReceive('getTractoringSpacecraft')
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

        $target->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(77777);
        $target->shouldReceive('isCloaked')
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

    #[DataProvider('provideGetAttackersAndDefendersData')]
    public function testGetAttackersAndDefenders(
        string $className,
        int $attackersCount,
        int $defendersCount,
        bool $expectedIsFleet
    ): void {
        /** @var FleetWrapperInterface|ShipWrapperInterface */
        $wrapper = $this->mock($className);
        $factory = $this->mock(BattlePartyFactoryInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $attackingParty = $this->mock(AttackingBattleParty::class);
        $attackedParty = $this->mock(AttackedBattleParty::class);

        $factory->shouldReceive('createAttackingBattleParty')
            ->with($wrapper, false)
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
        ] = $this->subject->getAttackersAndDefenders($wrapper, $targetWrapper, false, $factory);

        $this->assertEquals($attackingParty, $attackers);
        $this->assertEquals($attackedParty, $defenders);
        $this->assertTrue($isFleetFight === $expectedIsFleet);
    }

    public function testIsBoardingPossibleExpectFalseWhenTrumfield(): void
    {
        $nfsItem = $this->mock(TrumfieldNfsItem::class);

        $result = FightLib::isBoardingPossible($nfsItem);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenTholianWeb(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::THOLIAN_WEB);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenStation(): void
    {
        $station = $this->mock(Station::class);

        $station->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::STATION);

        $result = FightLib::isBoardingPossible($station);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenNpc(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::SHIP);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_FIRST_ID - 1);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenCloaked(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::SHIP);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_FIRST_ID);

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenShieldsOn(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::SHIP);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_FIRST_ID);

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isShielded')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = FightLib::isBoardingPossible($ship);

        $this->assertFalse($result);
    }

    public function testIsBoardingPossibleExpectFalseWhenWarped(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftTypeEnum::SHIP);

        $ship->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(UserConstants::USER_FIRST_ID);

        $ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $ship->shouldReceive('isShielded')
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
