<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Mockery\MockInterface;
use Override;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Entity\User;
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

    #[Override]
    public function setUp(): void
    {
        $this->researchRepository = $this->mock(ResearchRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->calculator = new ColonyLimitCalculator(
            $this->researchRepository,
            $this->colonyRepository
        );
    }

    public function testCanColonizeFurtherColonyWithTypeReturnsFalseIfExceeded(): void
    {
        $user = $this->mock(User::class);
        $type = ColonyTypeEnum::COLONY_TYPE_ASTEROID;

        //use new function
        $this->researchRepository->shouldReceive('getColonyTypeLimitByUser')
            ->with($user, $type)
            ->once()
            ->andReturn(1);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user, $type)
            ->once()
            ->andReturn(1);

        $this->assertFalse(
            $this->calculator->canColonizeFurtherColonyWithType($user, $type)
        );
    }


    public function testCanColonizeFurtherPlanetsReturnsTrueIfPossible(): void
    {
        $user = $this->mock(User::class);
        $type = ColonyTypeEnum::COLONY_TYPE_ASTEROID;

        //use new function
        $this->researchRepository->shouldReceive('getColonyTypeLimitByUser')
            ->with($user, $type)
            ->once()
            ->andReturn(2);

        $this->colonyRepository->shouldReceive('getAmountByUser')
            ->with($user, $type)
            ->once()
            ->andReturn(1);

        $this->assertTrue(
            $this->calculator->canColonizeFurtherColonyWithType($user, $type)
        );
    }
}
