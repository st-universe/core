<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Override;
use Mockery\MockInterface;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyClassResearchInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class ColonizationCheckerTest extends StuTestCase
{
    /**
     * @var ResearchedRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $researchedRepository;

    /**
     * @var ColonyClassResearchRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $colonyClassResearchRepository;

    /**
     * @var ColonyLimitCalculatorInterface|MockInterface|null
     */
    private ?MockInterface $colonyLimitCalculator;

    private ?ColonizationChecker $checker;

    #[Override]
    public function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->colonyClassResearchRepository = $this->mock(ColonyClassResearchRepositoryInterface::class);
        $this->colonyLimitCalculator = $this->mock(ColonyLimitCalculatorInterface::class);

        $this->checker = new ColonizationChecker(
            $this->researchedRepository,
            $this->colonyClassResearchRepository,
            $this->colonyLimitCalculator
        );
    }

    public function testCanColonizeReturnsFalseIfNotFree(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->checker->canColonize($user, $colony)
        );
    }

    public function testCanColonizeReturnsFalseIfLimitIsExceeded(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $type = ColonyTypeEnum::COLONY_TYPE_MOON;

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getColonyClass->getType')
            ->withNoArgs()
            ->once()
            ->andReturn($type);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherColonyWithType')
            ->with($user, $type)
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->checker->canColonize($user, $colony)
        );
    }

    public function testCanColonizeReturnsFalseIfNeedsResearchAndNotResearched(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $colonyClassResearch = $this->mock(ColonyClassResearchInterface::class);
        $colonyClass = $this->mock(ColonyClassInterface::class);
        $type = ColonyTypeEnum::COLONY_TYPE_MOON;

        $researchId = 666;

        $colonyClass->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn($type);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getColonyClass')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyClass);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherColonyWithType')
            ->with($user, $type)
            ->once()
            ->andReturnTrue();

        $this->colonyClassResearchRepository->shouldReceive('getByColonyClass')
            ->with($colonyClass)
            ->once()
            ->andReturn([$colonyClassResearch]);

        $colonyClassResearch->shouldReceive('getResearch->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($researchId);

        $this->researchedRepository->shouldReceive('hasUserFinishedResearch')
            ->with($user, [$researchId])
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->checker->canColonize($user, $colony)
        );
    }

    public function testCanColonizeReturnsTrueIfNeedsResearchAndIsResearched(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $colonyClassResearch = $this->mock(ColonyClassResearchInterface::class);
        $colonyClass = $this->mock(ColonyClassInterface::class);
        $type = ColonyTypeEnum::COLONY_TYPE_MOON;

        $researchId = 666;

        $colonyClass->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn($type);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getColonyClass')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyClass);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherColonyWithType')
            ->with($user, $type)
            ->once()
            ->andReturnTrue();

        $this->colonyClassResearchRepository->shouldReceive('getByColonyClass')
            ->with($colonyClass)
            ->once()
            ->andReturn([$colonyClassResearch]);

        $colonyClassResearch->shouldReceive('getResearch->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($researchId);

        $this->researchedRepository->shouldReceive('hasUserFinishedResearch')
            ->with($user, [$researchId])
            ->once()
            ->andReturnTrue();

        $this->assertTrue(
            $this->checker->canColonize($user, $colony)
        );
    }

    public function testCanColonizeReturnsTrueIfNoResearchNeeded(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $colonyClass = $this->mock(ColonyClassInterface::class);
        $type = ColonyTypeEnum::COLONY_TYPE_MOON;

        $colonyClass->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn($type);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getColonyClass')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyClass);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherColonyWithType')
            ->with($user, $type)
            ->once()
            ->andReturnTrue();

        $this->colonyClassResearchRepository->shouldReceive('getByColonyClass')
            ->with($colonyClass)
            ->once()
            ->andReturn([]);

        $this->assertTrue(
            $this->checker->canColonize($user, $colony)
        );
    }
}
