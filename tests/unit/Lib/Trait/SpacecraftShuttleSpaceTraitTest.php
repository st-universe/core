<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\StuTestCase;

class SpacecraftShuttleSpaceTraitTest extends StuTestCase
{
    use SpacecraftShuttleSpaceTrait;

    private MockInterface&Spacecraft $spacecraft;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraft = $this->mock(Spacecraft::class);
    }

    public function testGetStoredShuttleCountExpectZeroWhenStorageEmpty(): void
    {
        $this->spacecraft->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->getStoredShuttleCount($this->spacecraft);

        $this->assertEquals(0, $result);
    }

    public function testGetStoredShuttleCountExpectCorrectAmount(): void
    {
        $storageNoShuttle = $this->mock(Storage::class);
        $storageShuttle2 = $this->mock(Storage::class);
        $storageShuttle3 = $this->mock(Storage::class);

        $this->spacecraft->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$storageNoShuttle, $storageShuttle2, $storageShuttle3]));

        $storageNoShuttle->shouldReceive('getCommodity->isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $storageShuttle2->shouldReceive('getCommodity->isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $storageShuttle3->shouldReceive('getCommodity->isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $storageShuttle2->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $storageShuttle3->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $result = $this->getStoredShuttleCount($this->spacecraft);

        $this->assertEquals(5, $result);
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
        $storageShuttle42 = $this->mock(Storage::class);

        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getRump->getShuttleSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->spacecraft->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$storageShuttle42]));

        $storageShuttle42->shouldReceive('getCommodity->isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $storageShuttle42->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->hasFreeShuttleSpace($this->spacecraft);

        $this->assertFalse($result);
    }

    public function testHasFreeShuttleSpaceExpectTrueWhenSpaceLeft(): void
    {
        $storageShuttle42 = $this->mock(Storage::class);

        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getRump->getShuttleSlots')
            ->withNoArgs()
            ->once()
            ->andReturn(43);
        $this->spacecraft->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$storageShuttle42]));

        $storageShuttle42->shouldReceive('getCommodity->isShuttle')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $storageShuttle42->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $result = $this->hasFreeShuttleSpace($this->spacecraft);

        $this->assertTrue($result);
    }
}
