<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\SpacecraftAttacker;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class SpacecraftAttackCycleTest extends StuTestCase
{
    /** @var MockInterface&EnergyWeaponPhaseInterface */
    private $energyWeaponPhase;
    /** @var MockInterface&ProjectileWeaponPhaseInterface */
    private $projectileWeaponPhase;
    /** @var MockInterface&AttackerProviderFactoryInterface */
    private $attackerProviderFactory;
    /** @var MockInterface&AttackMatchupInterface */
    private $attackMatchup;
    /** @var MockInterface&BattlePartyFactoryInterface */
    private $battlePartyFactory;
    /** @var MockInterface&SpacecraftAttackPreparationInterface */
    private $spacecraftAttackPreparation;
    /** @var MockInterface&MessageFactoryInterface */
    private $messageFactory;

    private SpacecraftAttackCycleInterface $subject;

    #[Override]
    public function setUp(): void
    {
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

        $roundBasedAttackers->shouldReceive('saveActiveMembers')
            ->withNoArgs()
            ->once();
        $roundBasedDefenders->shouldReceive('saveActiveMembers')
            ->withNoArgs()
            ->once();

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->once()
            ->andReturn($messages);

        $this->subject->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::BOARD_SHIP);
    }
}
