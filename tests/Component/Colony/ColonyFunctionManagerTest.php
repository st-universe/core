<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use Mockery\MockInterface;
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

        $functionId = 666;

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$functionId], [ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(42);

        static::assertTrue(
            $this->subject->hasActiveFunction($colony, $functionId, false)
        );
    }

    public function testHasActiveFunctionReturnsValueWithCache(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $functionId = 666;
        $colonyId = 21;

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->times(2)
            ->andReturn($colonyId);

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$functionId], [ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->hasActiveFunction($colony, $functionId)
        );
        static::assertFalse(
            $this->subject->hasActiveFunction($colony, $functionId)
        );
    }

    public function testHasFunctionReturnsValue(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $functionId = 666;

        $this->planetFieldRepository->shouldReceive('getCountByColonyAndBuildingFunctionAndState')
            ->with($colony, [$functionId], [ColonyFunctionManager::STATE_DISABLED, ColonyFunctionManager::STATE_ENABLED])
            ->once()
            ->andReturn(42);

        static::assertTrue(
            $this->subject->hasFunction($colony, $functionId)
        );
    }
}
