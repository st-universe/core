<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Component\Borg\BorgSphereAttackContext;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\SpacecraftAttacker;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class SpacecraftAttackCycleTest extends StuTestCase
{
    private MockInterface&EnergyWeaponPhaseInterface $energyWeaponPhase;
    private MockInterface&ProjectileWeaponPhaseInterface $projectileWeaponPhase;
    private MockInterface&AttackerProviderFactoryInterface $attackerProviderFactory;
    private MockInterface&AttackMatchupInterface $attackMatchup;
    private MockInterface&BattlePartyFactoryInterface $battlePartyFactory;
    private MockInterface&SpacecraftAttackPreparationInterface $spacecraftAttackPreparation;
    private MockInterface&MessageFactoryInterface $messageFactory;
    private BorgSphereAttackContext $borgSphereAttackContext;

    private SpacecraftAttackCycleInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->energyWeaponPhase = $this->mock(EnergyWeaponPhaseInterface::class);
        $this->projectileWeaponPhase = $this->mock(ProjectileWeaponPhaseInterface::class);
        $this->attackerProviderFactory = $this->mock(AttackerProviderFactoryInterface::class);
        $this->attackMatchup = $this->mock(AttackMatchupInterface::class);
        $this->battlePartyFactory = $this->mock(BattlePartyFactoryInterface::class);
        $this->spacecraftAttackPreparation = $this->mock(SpacecraftAttackPreparationInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);
        $this->borgSphereAttackContext = new BorgSphereAttackContext();

        $this->subject = new SpacecraftAttackCycle(
            $this->energyWeaponPhase,
            $this->projectileWeaponPhase,
            $this->attackerProviderFactory,
            $this->attackMatchup,
            $this->battlePartyFactory,
            $this->spacecraftAttackPreparation,
            $this->messageFactory,
            $this->borgSphereAttackContext
        );
    }

    public function testCycleExpectFirstAndSecondStrike(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $attacker = $this->mock(ShipWrapperInterface::class);
        $attackers = $this->mock(BattlePartyInterface::class);
        $roundBasedAttackers = $this->mock(RoundBasedBattleParty::class);
        $defenders = $this->mock(BattlePartyInterface::class);
        $roundBasedDefenders = $this->mock(RoundBasedBattleParty::class);
        $firstMatchup = $this->mock(Matchup::class);
        $spacecraftAttacker = $this->mock(SpacecraftAttacker::class);

        $this->spacecraftAttackPreparation->shouldReceive('getReady')
            ->with($attackers, $defenders, true, Mockery::any())
            ->once();

        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')
            ->with($attackers)
            ->once()
            ->andReturn($roundBasedAttackers);
        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')
            ->with($defenders)
            ->once()
            ->andReturn($roundBasedDefenders);
        $attackers->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([$attacker]));

        $this->attackMatchup->shouldReceive('getMatchup')
            ->with($roundBasedAttackers, $roundBasedDefenders, true, true)
            ->once()
            ->andReturn($firstMatchup);
        $this->attackMatchup->shouldReceive('getMatchup')
            ->with($roundBasedAttackers, $roundBasedDefenders, false, true)
            ->once()
            ->andReturn(null);

        $firstMatchup->shouldReceive('getDefenders')
            ->withNoArgs()
            ->once()
            ->andReturn($defenders);
        $firstMatchup->shouldReceive('getAttacker')
            ->withNoArgs()
            ->once()
            ->andReturn($attacker);
        $firstMatchup->shouldReceive('isAttackingShieldsOnly')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->attackerProviderFactory->shouldReceive('createSpacecraftAttacker')
            ->with($attacker, true)
            ->once()
            ->andReturn($spacecraftAttacker);

        $this->energyWeaponPhase->shouldReceive('fire')
            ->with($spacecraftAttacker, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP, $messages)
            ->once();
        $this->projectileWeaponPhase->shouldReceive('fire')
            ->with($spacecraftAttacker, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP, $messages)
            ->once();

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messages);

        $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP);
    }

    public function testCycleThrowsWhenMatchupLimitIsExceeded(): void
    {
        $messages = $this->mock(MessageCollectionInterface::class);
        $attacker = $this->mock(ShipWrapperInterface::class);
        $attackerLeader = $this->mock(ShipWrapperInterface::class);
        $attackerLeaderShip = $this->mock(Ship::class);
        $attackers = $this->mock(BattlePartyInterface::class);
        $roundBasedAttackers = $this->mock(RoundBasedBattleParty::class);
        $defenderLeader = $this->mock(ShipWrapperInterface::class);
        $defenderLeaderShip = $this->mock(Ship::class);
        $defenders = $this->mock(BattlePartyInterface::class);
        $roundBasedDefenders = $this->mock(RoundBasedBattleParty::class);
        $matchup = $this->mock(Matchup::class);
        $spacecraftAttacker = $this->mock(SpacecraftAttacker::class);

        $this->spacecraftAttackPreparation->shouldReceive('getReady')
            ->with($attackers, $defenders, true, Mockery::any())
            ->once();

        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')
            ->with($attackers)
            ->once()
            ->andReturn($roundBasedAttackers);
        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')
            ->with($defenders)
            ->once()
            ->andReturn($roundBasedDefenders);
        $attackers->shouldReceive('getActiveMembers')
            ->with(true)
            ->once()
            ->andReturn(new ArrayCollection([$attacker]));

        $this->attackMatchup->shouldReceive('getMatchup')
            ->with($roundBasedAttackers, $roundBasedDefenders, true, true)
            ->once()
            ->andReturn($matchup);
        $this->attackMatchup->shouldReceive('getMatchup')
            ->with($roundBasedAttackers, $roundBasedDefenders, false, true)
            ->once()
            ->andReturn($matchup);

        $matchup->shouldReceive('getDefenders')
            ->withNoArgs()
            ->once()
            ->andReturn($defenders);
        $matchup->shouldReceive('getAttacker')
            ->withNoArgs()
            ->once()
            ->andReturn($attacker);
        $matchup->shouldReceive('isAttackingShieldsOnly')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->attackerProviderFactory->shouldReceive('createSpacecraftAttacker')
            ->with($attacker, false)
            ->once()
            ->andReturn($spacecraftAttacker);

        $this->energyWeaponPhase->shouldReceive('fire')
            ->with($spacecraftAttacker, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP, $messages)
            ->once();
        $this->projectileWeaponPhase->shouldReceive('fire')
            ->with($spacecraftAttacker, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP, $messages)
            ->once();
        $attackers->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $attackers->shouldReceive('getLeader')
            ->withNoArgs()
            ->once()
            ->andReturn($attackerLeader);
        $attackerLeader->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($attackerLeaderShip);
        $attackerLeaderShip->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1001);
        $defenders->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(102);
        $defenders->shouldReceive('getLeader')
            ->withNoArgs()
            ->once()
            ->andReturn($defenderLeader);
        $defenderLeader->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($defenderLeaderShip);
        $defenderLeaderShip->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1002);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messages);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Attack cycle exceeded matchup limit of 1 for cause BOARD_SHIP, attackersUser=101 attackersLeader=1001 defendersUser=102 defendersLeader=1002$/');

        $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP);
    }

    /** @return iterable<string, array{bool, bool}> */
    public static function disablingShots(): iterable
    {
        yield 'energy shot stops torpedoes and counterfire' => [false, false];
        yield 'projectile shot stops counterfire' => [false, true];
        yield 'confirmed destruction continues the battle' => [true, false];
    }

    #[DataProvider('disablingShots')]
    public function testNewDeactivationStopsTheRemainingBattleOnly(bool $confirmed, bool $projectile): void
    {
        [$attackers, $defenders, $attacker, $messages, $roundAttackers, $roundDefenders] = $this->singleMatchup();
        $target = $this->mock(Ship::class);
        $target->shouldReceive('getId')->andReturn(42);
        $this->borgSphereAttackContext->begin($target, $confirmed, false);
        $this->energyWeaponPhase->shouldReceive('fire')->once()->with($attacker, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT, $messages)
            ->andReturnUsing(function () use ($projectile, $target): void {
                if (!$projectile) {
                    $this->borgSphereAttackContext->stopBattleAfterDisabling($target);
                }
            });
        if ($confirmed || $projectile) {
            $this->projectileWeaponPhase->shouldReceive('fire')->once()->with($attacker, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT, $messages)
                ->andReturnUsing(function () use ($projectile, $target): void {
                    if ($projectile) {
                        $this->borgSphereAttackContext->stopBattleAfterDisabling($target);
                    }
                });
        } else {
            $this->projectileWeaponPhase->shouldNotReceive('fire');
        }
        if ($confirmed) {
            $this->attackMatchup->shouldReceive('getMatchup')->once()->with($roundAttackers, $roundDefenders, false, false)->andReturnNull();
        }

        self::assertSame($messages, $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT));
        self::assertFalse($this->borgSphereAttackContext->isBattleStopped());
        self::assertSame($confirmed, $this->borgSphereAttackContext->allowsDestruction($target));
    }

    public function testBattleStopIsClearedEvenWhenTheWeaponPhaseThrows(): void
    {
        [$attackers, $defenders] = $this->singleMatchup();
        $target = $this->mock(Ship::class);
        $target->shouldReceive('getId')->andReturn(42);
        $this->energyWeaponPhase->shouldReceive('fire')->once()->andReturnUsing(function () use ($target): void {
            $this->borgSphereAttackContext->stopBattleAfterDisabling($target);
            throw new RuntimeException('weapon failure');
        });
        $this->projectileWeaponPhase->shouldNotReceive('fire');

        try {
            $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::SHIP_FIGHT);
            self::fail('The failed weapon phase must propagate its exception');
        } catch (RuntimeException $exception) {
            self::assertSame('weapon failure', $exception->getMessage());
        }
        self::assertFalse($this->borgSphereAttackContext->isBattleStopped());
    }

    /** @return array{BattlePartyInterface, BattlePartyInterface, SpacecraftAttacker, MessageCollectionInterface, RoundBasedBattleParty, RoundBasedBattleParty} */
    private function singleMatchup(): array
    {
        $attackers = $this->mock(BattlePartyInterface::class);
        $defenders = $this->mock(BattlePartyInterface::class);
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $attacker = $this->mock(SpacecraftAttacker::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $roundAttackers = $this->mock(RoundBasedBattleParty::class);
        $roundDefenders = $this->mock(RoundBasedBattleParty::class);
        $matchup = $this->mock(Matchup::class);
        $this->messageFactory->shouldReceive('createMessageCollection')->once()->andReturn($messages);
        $this->spacecraftAttackPreparation->shouldReceive('getReady')->once()->with($attackers, $defenders, false, $messages);
        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')->once()->with($attackers)->andReturn($roundAttackers);
        $this->battlePartyFactory->shouldReceive('createRoundBasedBattleParty')->once()->with($defenders)->andReturn($roundDefenders);
        $attackers->shouldReceive('getActiveMembers')->once()->with(true)->andReturn(new ArrayCollection([$wrapper]));
        $defenders->shouldReceive('getActiveMembers')->once()->with(true)->andReturn(new ArrayCollection([$wrapper]));
        $this->attackMatchup->shouldReceive('getMatchup')->once()->with($roundAttackers, $roundDefenders, true, false)->andReturn($matchup);
        $matchup->shouldReceive('getDefenders')->once()->andReturn($defenders);
        $matchup->shouldReceive('getAttacker')->once()->andReturn($wrapper);
        $matchup->shouldReceive('isAttackingShieldsOnly')->once()->andReturn(false);
        $this->attackerProviderFactory->shouldReceive('createSpacecraftAttacker')->once()->with($wrapper, false)->andReturn($attacker);
        return [$attackers, $defenders, $attacker, $messages, $roundAttackers, $roundDefenders];
    }
}
