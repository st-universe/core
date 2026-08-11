<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Crew;

use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\TroopQuartersShipSystem;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\StuTestCase;

class SpacecraftCrewCalculatorTest extends StuTestCase
{
    private SpacecraftCrewCalculatorInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->subject = new SpacecraftCrewCalculator();
    }

    public function testGetMaxCrewCountByRumpReturnsConfiguredMaximum(): void
    {
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);

        $rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->once()
            ->andReturn($baseValues);
        $baseValues->shouldReceive('getMaxCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertSame(42, $this->subject->getMaxCrewCountByRump($rump));
    }

    public function testGetMaxCrewCountByShipAddsTroopQuarters(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);

        $spacecraft->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(true);
        $spacecraft->shouldReceive('getNeededCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(13);
        $rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->once()
            ->andReturn($baseValues);
        $rump->shouldReceive('getRoleId')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftRumpRoleEnum::BASE);
        $baseValues->shouldReceive('getMaxCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertSame(42 + TroopQuartersShipSystem::QUARTER_COUNT_BASE, $this->subject->getMaxCrewCountByShip($spacecraft));
    }

    public function testGetMaxCrewCountByShipReturnsNeededCrewCountWhenItIsHigherThanMaximum(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $rump = $this->mock(SpacecraftRump::class);
        $baseValues = $this->mock(SpacecraftRumpBaseValues::class);

        $spacecraft->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            ->once()
            ->andReturn(false);
        $spacecraft->shouldReceive('getNeededCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(24);
        $rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->once()
            ->andReturn($baseValues);
        $baseValues->shouldReceive('getMaxCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(12);

        $this->assertSame(24, $this->subject->getMaxCrewCountByShip($spacecraft));
    }
}
