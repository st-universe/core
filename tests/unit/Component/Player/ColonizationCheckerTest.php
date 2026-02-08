<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Mockery\MockInterface;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassResearch;
use Stu\Orm\Entity\User;
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

    #[\Override]
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
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);

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
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $type = ColonyTypeEnum::MOON;

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
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $colonyClassResearch = $this->mock(ColonyClassResearch::class);
        $colonyClass = $this->mock(ColonyClass::class);
        $type = ColonyTypeEnum::MOON;

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
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $colonyClassResearch = $this->mock(ColonyClassResearch::class);
        $colonyClass = $this->mock(ColonyClass::class);
        $type = ColonyTypeEnum::MOON;

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
        $user = $this->mock(User::class);
        $colony = $this->mock(Colony::class);
        $colonyClass = $this->mock(ColonyClass::class);
        $type = ColonyTypeEnum::MOON;

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
