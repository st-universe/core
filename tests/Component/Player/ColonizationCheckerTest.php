<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Mockery\MockInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Entity\PlanetTypeResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PlanetTypeResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class ColonizationCheckerTest extends StuTestCase
{
    /**
     * @var ResearchedRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $researchedRepository;

    /**
     * @var PlanetTypeResearchRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $planetTypeResearchRepository;

    /**
     * @var ColonyLimitCalculatorInterface|MockInterface|null
     */
    private ?MockInterface $colonyLimitCalculator;

    private ?ColonizationChecker $checker;

    public function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->planetTypeResearchRepository = $this->mock(PlanetTypeResearchRepositoryInterface::class);
        $this->colonyLimitCalculator = $this->mock(ColonyLimitCalculatorInterface::class);

        $this->checker = new ColonizationChecker(
            $this->researchedRepository,
            $this->planetTypeResearchRepository,
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

    public function testCanColonizeReturnsFalseIfIsPlanetAndLimitIsExceeded(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getPlanetType->getIsMoon')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherPlanets')
            ->with($user)
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->checker->canColonize($user, $colony)
        );
    }

    public function testCanColonizeReturnsFalseIfIsMoonAndLimitIsExceeded(): void
    {
        $user = $this->mock(UserInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getPlanetType->getIsMoon')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherMoons')
            ->with($user)
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
        $planetTypeResearch = $this->mock(PlanetTypeResearchInterface::class);
        $planetType = $this->mock(PlanetTypeInterface::class);

        $researchId = 666;

        $planetType->shouldReceive('getIsMoon')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getPlanetType')
            ->withNoArgs()
            ->once()
            ->andReturn($planetType);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherMoons')
            ->with($user)
            ->once()
            ->andReturnTrue();

        $this->planetTypeResearchRepository->shouldReceive('getByPlanetType')
            ->with($planetType)
            ->once()
            ->andReturn([$planetTypeResearch]);

        $planetTypeResearch->shouldReceive('getResearch->getId')
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
        $planetTypeResearch = $this->mock(PlanetTypeResearchInterface::class);
        $planetType = $this->mock(PlanetTypeInterface::class);

        $researchId = 666;

        $planetType->shouldReceive('getIsMoon')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getPlanetType')
            ->withNoArgs()
            ->once()
            ->andReturn($planetType);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherMoons')
            ->with($user)
            ->once()
            ->andReturnTrue();

        $this->planetTypeResearchRepository->shouldReceive('getByPlanetType')
            ->with($planetType)
            ->once()
            ->andReturn([$planetTypeResearch]);

        $planetTypeResearch->shouldReceive('getResearch->getId')
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
        $planetType = $this->mock(PlanetTypeInterface::class);

        $planetType->shouldReceive('getIsMoon')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $colony->shouldReceive('isFree')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $colony->shouldReceive('getPlanetType')
            ->withNoArgs()
            ->once()
            ->andReturn($planetType);

        $this->colonyLimitCalculator->shouldReceive('canColonizeFurtherMoons')
            ->with($user)
            ->once()
            ->andReturnTrue();

        $this->planetTypeResearchRepository->shouldReceive('getByPlanetType')
            ->with($planetType)
            ->once()
            ->andReturn([]);

        $this->assertTrue(
            $this->checker->canColonize($user, $colony)
        );
    }
}
