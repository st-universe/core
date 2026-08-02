<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\TorpedoType;
use Stu\StuTestCase;

class SpacecraftStorageTorpedoLogicTest extends StuTestCase
{
    private MockInterface&Spacecraft $spacecraft;
    private MockInterface&SpacecraftRump $rump;
    private MockInterface&TorpedoType $torpedoType;
    private MockInterface&InformationInterface $information;

    private SpacecraftStorageTorpedoLogic $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraft = $this->mock(Spacecraft::class);
        $this->rump = $this->mock(SpacecraftRump::class);
        $this->torpedoType = $this->mock(TorpedoType::class);
        $this->information = $this->mock(InformationInterface::class);
        $this->subject = new SpacecraftStorageTorpedoLogic();
    }

    public function testHealthyTransportModuleAcceptsNonFireableTorpedoType(): void
    {
        $this->spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(true);
        $result = $this->subject->canStoreTorpedoType($this->spacecraft, $this->torpedoType, $this->information);

        $this->assertTrue($result);
    }

    public function testHealthyTransportModuleAcceptsSecondFireableTorpedoType(): void
    {
        $this->spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(true);
        $result = $this->subject->canStoreTorpedoType($this->spacecraft, $this->torpedoType, $this->information);

        $this->assertTrue($result);
    }

    public function testShipWithoutTransportModuleAcceptsSecondMatchingLevelType(): void
    {
        $this->spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(false);
        $this->spacecraft->shouldReceive('getRump')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->rump);
        $this->spacecraft->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturn(false);
        $this->rump->shouldReceive('getTorpedoLevel')
            ->withNoArgs()
            ->twice()
            ->andReturn(4);
        $this->torpedoType->shouldReceive('getLevel')
            ->withNoArgs()
            ->twice()
            ->andReturn(4);

        $result = $this->subject->canStoreTorpedoType($this->spacecraft, $this->torpedoType, $this->information);

        $this->assertTrue($result);
    }
}
