<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
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
use Stu\Orm\Entity\User;
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

        $this->subject = new SpacecraftAttackCycle(
            $this->energyWeaponPhase,
            $this->projectileWeaponPhase,
            $this->attackerProviderFactory,
            $this->attackMatchup,
            $this->battlePartyFactory,
            $this->spacecraftAttackPreparation,
            $this->messageFactory
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
        $attackerUser = $this->mock(User::class);
        $attackers = $this->mock(BattlePartyInterface::class);
        $roundBasedAttackers = $this->mock(RoundBasedBattleParty::class);
        $defenderLeader = $this->mock(ShipWrapperInterface::class);
        $defenderLeaderShip = $this->mock(Ship::class);
        $defenderUser = $this->mock(User::class);
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
        $this->expectExceptionMessage('Attack cycle exceeded matchup limit of 1 for cause BOARD_SHIP, attackersUser=101 attackersLeader=1001 defendersUser=102 defendersLeader=1002');

        $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP);
    }
}
