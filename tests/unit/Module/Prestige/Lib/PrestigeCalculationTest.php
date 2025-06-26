<?php

declare(strict_types=1);

namespace Stu\Module\Prestige\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Override;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class PrestigeCalculationTest extends StuTestCase
{
    private PrestigeCalculationInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new PrestigeCalculation();
    }

    public function testGetPrestigeOfSpacecraftOrFleetExpectSingleShipPrestige(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getPrestigeOfSpacecraftOrFleet($ship);

        $this->assertEquals(42, $result);
    }

    public function testGetPrestigeOfSpacecraftOrFleetExpectSingleWrapperPrestige(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $ship = $this->mock(Ship::class);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->subject->getPrestigeOfSpacecraftOrFleet($wrapper);

        $this->assertEquals(42, $result);
    }

    public function testGetPrestigeOfSpacecraftOrFleetExpectFleetPrestige(): void
    {
        $ship1 = $this->mock(Ship::class);
        $ship2 = $this->mock(Ship::class);
        $fleet = $this->mock(Fleet::class);

        $ship1->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);

        $fleet->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([0 => $ship1, 2 => $ship2]));

        $ship1->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $ship2->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->getPrestigeOfSpacecraftOrFleet($ship1);

        $this->assertEquals(43, $result);
    }

    public function testTargetHasPositivePrestigeExpectFalseForSingleShip(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $result = $this->subject->targetHasPositivePrestige($ship);

        $this->assertFalse($result);
    }

    public function testTargetHasPositivePrestigeExpectTrueForSingleShip(): void
    {
        $ship = $this->mock(Ship::class);

        $ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $ship->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->targetHasPositivePrestige($ship);

        $this->assertTrue($result);
    }

    public function testTargetHasPositivePrestigeExpectTrueForFleet(): void
    {
        $ship1 = $this->mock(Ship::class);
        $ship2 = $this->mock(Ship::class);
        $ship3 = $this->mock(Ship::class);
        $fleet = $this->mock(Fleet::class);

        $ship1->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);

        $fleet->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                0 => $ship1,
                2 => $ship2,
                3 => $ship3
            ]));

        $ship1->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $ship2->shouldReceive('getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->targetHasPositivePrestige($ship1);

        $this->assertTrue($result);
    }

    public function testGetPrestigeOfBattleParty(): void
    {
        $wrapper1 = $this->mock(ShipWrapperInterface::class);
        $wrapper2 = $this->mock(ShipWrapperInterface::class);
        $battleParty = $this->mock(BattlePartyInterface::class);

        $battleParty->shouldReceive('getActiveMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                0 => $wrapper1,
                2 => $wrapper2
            ]));

        $wrapper1->shouldReceive('get->getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $wrapper2->shouldReceive('get->getRump->getPrestige')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->getPrestigeOfBattleParty($battleParty);

        $this->assertEquals(43, $result);
    }
}
