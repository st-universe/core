<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery\MockInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\StuTestCase;

class AttackMatchupTest extends StuTestCase
{
    /** @var MockInterface|FightLibInterface */
    private FightLibInterface $fightLib;

    /** @var MockInterface|StuRandom */
    private StuRandom $stuRandom;

    private AttackMatchupInterface $subject;

    public function setUp(): void
    {
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new AttackMatchup(
            $this->fightLib,
            $this->stuRandom
        );
    }

    public function testGetMatchupExpectNullWhenAllShipsUsed(): void
    {
        $usedShipIds = [1, 2, 3, 4];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $attacker2 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);
        $defender2 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $attacker2->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $defender2->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $attackers = [$attacker1, $attacker2];
        $defenders = [$defender1, $defender2];

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectNullWhenAllShipsInactive(): void
    {
        $usedShipIds = [1];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $attackers = [$attacker1];
        $defenders = [$defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn([]);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn([]);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectAttackingAttackerIfFirstStrike(): void
    {
        $usedShipIds = [];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $attacker2 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $attacker2->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1, 2 => $attacker2];
        $defenders = [$defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn([$defender1]);

        $this->stuRandom->shouldReceive('array_rand')
            ->with($attackers)
            ->once()
            ->andReturn(2);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds, true);

        $this->assertNotNull($matchup);
        $this->assertEquals($attacker2, $matchup->getAttacker());
        $this->assertEquals([$defender1], $matchup->getDefenders());
        $this->assertEquals([2], $usedShipIds);
    }

    public function testGetMatchupExpectNullIfNobodyCanFire(): void
    {
        $usedShipIds = [];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1];
        $defenders = [2 => $defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);

        $this->fightLib->shouldReceive('canFire')
            ->with($attacker1)
            ->once()
            ->andReturn(false);
        $this->fightLib->shouldReceive('canFire')
            ->with($defender1)
            ->once()
            ->andReturn(false);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectNullIfOnlyDefenderActiveButOneWay(): void
    {
        $usedShipIds = [1];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1];
        $defenders = [2 => $defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds, false, true);

        $this->assertNull($matchup);
    }

    public function testGetMatchupExpectAttackingDefenderIfAttackersNotReady(): void
    {
        $usedShipIds = [];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1];
        $defenders = [2 => $defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);

        $this->fightLib->shouldReceive('canFire')
            ->with($attacker1)
            ->once()
            ->andReturn(false);
        $this->fightLib->shouldReceive('canFire')
            ->with($defender1)
            ->once()
            ->andReturn(true);

        $this->stuRandom->shouldReceive('array_rand')
            ->with($defenders)
            ->once()
            ->andReturn(2);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNotNull($matchup);
        $this->assertEquals($defender1, $matchup->getAttacker());
        $this->assertEquals($attackers, $matchup->getDefenders());
        $this->assertEquals([2], $usedShipIds);
    }

    public function testGetMatchupExpectAttackingAttackerIfDefendersNotReady(): void
    {
        $usedShipIds = [];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1];
        $defenders = [2 => $defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);

        $this->fightLib->shouldReceive('canFire')
            ->with($attacker1)
            ->once()
            ->andReturn(true);
        $this->fightLib->shouldReceive('canFire')
            ->with($defender1)
            ->once()
            ->andReturn(false);

        $this->stuRandom->shouldReceive('array_rand')
            ->with($attackers)
            ->once()
            ->andReturn(1);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNotNull($matchup);
        $this->assertEquals($attacker1, $matchup->getAttacker());
        $this->assertEquals($defenders, $matchup->getDefenders());
        $this->assertEquals([1], $usedShipIds);
    }

    public static function provideGetMatchupExpectRandomShooterIfBothSidesReadyData()
    {
        return [[1], [2]];
    }

    /**
     * @dataProvider provideGetMatchupExpectRandomShooterIfBothSidesReadyData
     */
    public function testGetMatchupExpectRandomShooterIfBothSidesReady(int $randomNumber): void
    {
        $usedShipIds = [];

        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);

        $attacker1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(1);
        $defender1->shouldReceive('get->getId')
            ->withNoArgs()
            ->andReturn(2);

        $attackers = [1 => $attacker1];
        $defenders = [2 => $defender1];

        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($attackers)
            ->once()
            ->andReturn($attackers);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);

        $this->fightLib->shouldReceive('canFire')
            ->with($attacker1)
            ->once()
            ->andReturn(true);
        $this->fightLib->shouldReceive('canFire')
            ->with($defender1)
            ->once()
            ->andReturn(true);

        $this->stuRandom->shouldReceive('array_rand')
            ->with($randomNumber === 1 ? $attackers : $defenders)
            ->once()
            ->andReturn($randomNumber);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 2)
            ->once()
            ->andReturn($randomNumber);

        $matchup = $this->subject->getMatchup($attackers, $defenders, $usedShipIds);

        $this->assertNotNull($matchup);
        $this->assertEquals([$randomNumber], $usedShipIds);
    }
}
