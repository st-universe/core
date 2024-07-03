<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery;
use Mockery\MockInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Party\RoundBasedBattleParty;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Provider\ShipAttacker;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class ShipAttackCycleTest extends StuTestCase
{
    /** @var MockInterface|EnergyWeaponPhaseInterface */
    private $energyWeaponPhase;
    /** @var MockInterface|ProjectileWeaponPhaseInterface */
    private $projectileWeaponPhase;
    /** @var MockInterface|AttackerProviderFactoryInterface */
    private $attackerProviderFactory;
    /** @var MockInterface|AttackMatchupInterface */
    private $attackMatchup;
    /** @var MockInterface|BattlePartyFactoryInterface */
    private $battlePartyFactory;
    /** @var MockInterface|ShipAttackPreparationInterface */
    private $shipAttackPreparation;
    /** @var MockInterface|MessageFactoryInterface */
    private $messageFactory;

    private ShipAttackCycleInterface $subject;

    public function setUp(): void
    {
        $this->energyWeaponPhase = $this->mock(EnergyWeaponPhaseInterface::class);
        $this->projectileWeaponPhase = $this->mock(ProjectileWeaponPhaseInterface::class);
        $this->attackerProviderFactory = $this->mock(AttackerProviderFactoryInterface::class);
        $this->attackMatchup = $this->mock(AttackMatchupInterface::class);
        $this->battlePartyFactory = $this->mock(BattlePartyFactoryInterface::class);
        $this->shipAttackPreparation = $this->mock(ShipAttackPreparationInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->subject = new ShipAttackCycle(
            $this->energyWeaponPhase,
            $this->projectileWeaponPhase,
            $this->attackerProviderFactory,
            $this->attackMatchup,
            $this->battlePartyFactory,
            $this->shipAttackPreparation,
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
        $shipAttacker = $this->mock(ShipAttacker::class);

        $this->shipAttackPreparation->shouldReceive('getReady')
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

        $this->attackerProviderFactory->shouldReceive('getShipAttacker')
            ->with($attacker)
            ->once()
            ->andReturn($shipAttacker);

        $this->energyWeaponPhase->shouldReceive('fire')
            ->with($shipAttacker, $defenders, ShipAttackCauseEnum::BOARD_SHIP, $messages)
            ->once();
        $this->projectileWeaponPhase->shouldReceive('fire')
            ->with($shipAttacker, $defenders, ShipAttackCauseEnum::BOARD_SHIP, $messages)
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

        $collection = $this->subject->cycle($attackers, $defenders, ShipAttackCauseEnum::BOARD_SHIP);
    }
}
