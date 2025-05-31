<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\StuTestCase;

class SpacecraftHasFreeShuttleSpaceTraitTest extends StuTestCase
{
    use SpacecraftHasFreeShuttleSpaceTrait;

    /** @var MockInterface&SpacecraftInterface */
    private $spacecraft;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraft = $this->mock(SpacecraftInterface::class);
    }

    public function testHasFreeShuttleSpaceExpectFalseWhenNoShuttleRampInstalled(): void
    {
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            ->once()
            ->andReturn(false);

        $result = $this->hasFreeShuttleSpace($this->spacecraft);

        $this->assertFalse($result);
    }

    public function testHasFreeShuttleSpaceExpectFalseWhenNoSpaceLeft(): void
    {
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getStoredShuttleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->spacecraft->shouldReceive('getRump->getShuttleSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->hasFreeShuttleSpace($this->spacecraft);

        $this->assertFalse($result);
    }

    public function testHasFreeShuttleSpaceExpectTrueWhenSpaceLeft(): void
    {
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getStoredShuttleCount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->spacecraft->shouldReceive('getRump->getShuttleSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(43);

        $result = $this->hasFreeShuttleSpace($this->spacecraft);

        $this->assertTrue($result);
    }
}
