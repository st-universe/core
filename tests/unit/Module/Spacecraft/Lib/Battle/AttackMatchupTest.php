<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\RoundBasedBattleParty;
use Stu\StuTestCase;

class AttackMatchupTest extends StuTestCase
{
    private MockInterface&StuRandom $stuRandom;

    private AttackMatchupInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new AttackMatchup(
            $this->stuRandom
        );
    }

    public function testGetMatchupExpectNullWhenBothPartiesDone(): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $defenders = $this->mock(RoundBasedBattleParty::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $defenders->shouldReceive('isDone')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $matchup = $this->subject->getMatchup($attackers, $defenders);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectAttackingAttackerIfFirstStrike(): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $defenders = $this->mock(RoundBasedBattleParty::class);
        $defenderBattleParty = $this->mock(BattlePartyInterface::class);
        $randomAttacker = $this->mock(ShipWrapperInterface::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->twice()
            ->andReturn(false);
        $attackers->shouldReceive('getRandomUnused')
            ->withNoArgs()
            ->once()
            ->andReturn($randomAttacker);

        $defenders->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($defenderBattleParty);

        $matchup = $this->subject->getMatchup($attackers, $defenders, true);

        $this->assertNotNull($matchup);
        $this->assertEquals($randomAttacker, $matchup->getAttacker());
        $this->assertEquals($defenderBattleParty, $matchup->getDefenders());
    }

    public function testGetMatchupExpectNullIfOnlyDefenderActiveButOneWay(): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $defenders = $this->mock(RoundBasedBattleParty::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->twice()
            ->andReturn(true);
        $defenders->shouldReceive('isDone')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $matchup = $this->subject->getMatchup($attackers, $defenders, false, true);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectAttackingDefenderIfAttackersNotReady(): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $attackerBattleParty = $this->mock(BattlePartyInterface::class);
        $defenders = $this->mock(RoundBasedBattleParty::class);
        $randomDefender = $this->mock(ShipWrapperInterface::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(true);
        $attackers->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($attackerBattleParty);

        $defenders->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(false);
        $defenders->shouldReceive('getRandomUnused')
            ->withNoArgs()
            ->once()
            ->andReturn($randomDefender);

        $matchup = $this->subject->getMatchup($attackers, $defenders);

        $this->assertNotNull($matchup);
        $this->assertEquals($randomDefender, $matchup->getAttacker());
        $this->assertEquals($attackerBattleParty, $matchup->getDefenders());
    }

    public function testGetMatchupExpectAttackingAttackerIfDefendersNotReady(): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $randomAttacker = $this->mock(ShipWrapperInterface::class);
        $defenders = $this->mock(RoundBasedBattleParty::class);
        $defenderBattleParty = $this->mock(BattlePartyInterface::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(false);
        $attackers->shouldReceive('getRandomUnused')
            ->withNoArgs()
            ->once()
            ->andReturn($randomAttacker);

        $defenders->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(true);
        $defenders->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($defenderBattleParty);

        $matchup = $this->subject->getMatchup($attackers, $defenders);

        $this->assertNotNull($matchup);
        $this->assertEquals($randomAttacker, $matchup->getAttacker());
        $this->assertEquals($defenderBattleParty, $matchup->getDefenders());
    }

    public static function provideGetMatchupExpectRandomShooterIfBothSidesReadyData(): array
    {
        return [[1], [2]];
    }

    #[DataProvider('provideGetMatchupExpectRandomShooterIfBothSidesReadyData')]
    public function testGetMatchupExpectRandomShooterIfBothSidesReady(int $randomNumber): void
    {
        $attackers = $this->mock(RoundBasedBattleParty::class);
        $attackerBattleParty = $this->mock(BattlePartyInterface::class);

        $defenders = $this->mock(RoundBasedBattleParty::class);
        $defenderBattleParty = $this->mock(BattlePartyInterface::class);

        $attackers->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(false);

        $defenders->shouldReceive('isDone')
            ->withNoArgs()
            ->andReturn(false);

        if ($randomNumber === 1) {
            $defenders->shouldReceive('get')
                ->withNoArgs()
                ->once()
                ->andReturn($defenderBattleParty);
        } else {
            $attackers->shouldReceive('get')
                ->withNoArgs()
                ->once()
                ->andReturn($attackerBattleParty);
        }

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 2)
            ->once()
            ->andReturn($randomNumber);

        $matchup = $this->subject->getMatchup($attackers, $defenders);

        $this->assertNotNull($matchup);
        $this->assertEquals(
            $randomNumber === 1 ? $attackerBattleParty : $defenderBattleParty,
            $matchup->getDefenders()
        );
    }
}
