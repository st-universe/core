<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Mockery\MockInterface;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ColonyFunctionManagerTest extends StuTestCase
{
    /** @var PlanetFieldRepositoryInterface&MockInterface */
    private MockInterface $planetFieldRepository;

    private ColonyFunctionManager $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);

        $this->subject = new ColonyFunctionManager(
            $this->planetFieldRepository
        );
    }

    public function testHasActiveFunctionReturnsValueWithoutCache(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $function = BuildingFunctionEnum::BASE_CAMP;

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$function], [ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(42);

        static::assertTrue(
            $this->subject->hasActiveFunction($colony, $function, false)
        );
    }

    public function testHasActiveFunctionReturnsValueWithCache(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $function = BuildingFunctionEnum::BASE_CAMP;
        $colonyId = 21;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn($colonyId);

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$function], [ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->hasActiveFunction($colony, $function)
        );
        static::assertFalse(
            $this->subject->hasActiveFunction($colony, $function)
        );
    }

    public function testHasFunctionReturnsValue(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $function = BuildingFunctionEnum::BASE_CAMP;

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$function], [ColonyFunctionManager::STATE_DISABLED, ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(42);

        static::assertTrue(
            $this->subject->hasFunction($colony, $function)
        );
    }
}
