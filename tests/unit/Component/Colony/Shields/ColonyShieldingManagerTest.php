<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Mockery\MockInterface;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ColonyShieldingManagerTest extends StuTestCase
{
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;

    private MockInterface&ColonyFunctionManagerInterface $colonyFunctionManager;

    private MockInterface&ColonyInterface $colony;

    private ColonyShieldingManager $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->colony = $this->mock(ColonyInterface::class);

        $this->subject = new ColonyShieldingManager(
            $this->planetFieldRepository,
            $this->colonyFunctionManager,
            $this->colony
        );
    }

    public function testHasShieldingReturnsValue(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasFunction')
            ->with(
                $this->colony,
                BuildingFunctionEnum::SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        static::assertTrue(
            $this->subject->hasShielding()
        );
    }

    public function testGetMaxShieldingReturnsValue(): void
    {
        $value = 42;

        $this->planetFieldRepository->shouldReceive('getMaxShieldsOfHost')
            ->with($this->colony)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getMaxShielding()
        );
    }

    public function testIsShieldingEnabledReturnsFalseIfNoActiveShieldGeneratorExists(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingFunctionEnum::SHIELD_GENERATOR
            )
            ->once()
            ->andReturnFalse();

        static::assertFalse(
            $this->subject->isShieldingEnabled()
        );
    }

    public function testIsShieldingEnabledReturnsFalseIfShieldIsNotLoaded(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingFunctionEnum::SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        $this->colony->shouldReceive('getShields')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->isShieldingEnabled()
        );
    }

    public function testIsShieldingEnabledReturnsTrueIfActive(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingFunctionEnum::SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        $this->colony->shouldReceive('getShields')
            ->withNoArgs()
            ->once()
            ->andReturn(52);

        static::assertTrue(
            $this->subject->isShieldingEnabled()
        );
    }
}
