<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\StuTestCase;

class ColonyLimitCalculatorTest extends StuTestCase
{
    /**
     * @var ResearchRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $researchRepository;

    /**
     * @var ColonyRepositoryInterface|MockInterface|null
     */
    private ?MockInterface $colonyRepository;

    private ?ColonyLimitCalculator $calculator;

    public function setUp(): void
    {
        $this->researchRepository = $this->mock(ResearchRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->calculator = new ColonyLimitCalculator(
            $this->researchRepository,
            $this->colonyRepository
        );
    }

    public function testCanColonizeFurtherPlanetsReturnsFalseIfExceeded(): void
    {
        $user = $this->mock(UserInterface::class);

        //use new function
        $this->researchRepository->shouldReceive('getPlanetColonyLimitByUser')
            ->with($user)
            ->once()
            ->andReturn(1);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user)
            ->once()
            ->andReturn(1);

        $this->assertFalse(
            $this->calculator->canColonizeFurtherPlanets($user)
        );
    }


    public function testCanColonizeFurtherPlanetsReturnsTrueIfPossible(): void
    {
        $user = $this->mock(UserInterface::class);

        //use new function
        $this->researchRepository->shouldReceive('getPlanetColonyLimitByUser')
            ->with($user)
            ->once()
            ->andReturn(2);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user)
            ->once()
            ->andReturn(1);

        $this->assertTrue(
            $this->calculator->canColonizeFurtherPlanets($user)
        );
    }

    public function testCanColonizeFurtherMoonsReturnsFalseIfExceeded(): void
    {
        $user = $this->mock(UserInterface::class);

        //use new function
        $this->researchRepository->shouldReceive('getMoonColonyLimitByUser')
            ->with($user)
            ->once()
            ->andReturn(1);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user, true)
            ->once()
            ->andReturn(1);

        $this->assertFalse(
            $this->calculator->canColonizeFurtherMoons($user)
        );
    }


    public function testCanColonizeFurtherMoonsReturnsTrueIfPossible(): void
    {
        $user = $this->mock(UserInterface::class);

        //use new function
        $this->researchRepository->shouldReceive('getMoonColonyLimitByUser')
            ->with($user)
            ->once()
            ->andReturn(2);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user, true)
            ->once()
            ->andReturn(1);

        $this->assertTrue(
            $this->calculator->canColonizeFurtherMoons($user)
        );
    }
}
