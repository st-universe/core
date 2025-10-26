<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Mockery\MockInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\StuTestCase;

class CrewCountRetrieverTest extends StuTestCase
{
    private MockInterface&CrewRepositoryInterface $crewRepository;

    private MockInterface&CrewAssignmentRepositoryInterface $shipCrewRepository;

    private MockInterface&CrewTrainingRepositoryInterface $crewTrainigRepository;

    private MockInterface&CrewLimitCalculatorInterface $crewLimitCalculator;

    private CrewCountRetriever $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->shipCrewRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->crewTrainigRepository = $this->mock(CrewTrainingRepositoryInterface::class);
        $this->crewLimitCalculator = $this->mock(CrewLimitCalculatorInterface::class);

        $this->subject = new CrewCountRetriever(
            $this->crewRepository,
            $this->shipCrewRepository,
            $this->crewLimitCalculator,
            $this->crewTrainigRepository
        );
    }

    public function testGetDebrisAndTradePostsCountReturnsValue(): void
    {
        $user = $this->mock(User::class);

        $amount_tradeposts = 666;
        $amount_debris = 42;

        $this->crewRepository->shouldReceive('getAmountByUserAndShipRumpCategory')
            ->with($user, SpacecraftRumpCategoryEnum::ESCAPE_PODS)
            ->once()
            ->andReturn($amount_debris);

        $this->shipCrewRepository->shouldReceive('getAmountByUserAtTradeposts')
            ->with($user)
            ->once()
            ->andReturn($amount_tradeposts);

        static::assertSame(
            $amount_debris + $amount_tradeposts,
            $this->subject->getDebrisAndTradePostsCount($user)
        );
    }

    public function testGetAssignedToShipsCountReturnsValue(): void
    {
        $value = 666;

        $user = $this->mock(User::class);

        $this->shipCrewRepository->shouldReceive('getAmountByUserOnShips')
            ->with($user)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getAssignedToShipsCount($user)
        );
    }

    public function testGetInTrainingCountReturnsValue(): void
    {
        $user = $this->mock(User::class);

        $value = 666;

        $this->crewTrainigRepository->shouldReceive('getCountByUser')
            ->with($user)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getInTrainingCount($user)
        );
    }

    public function testGetRemainingCountReturnsZeroIfNotPositive(): void
    {
        $user = $this->mock(User::class);

        $this->crewLimitCalculator->shouldReceive('getGlobalCrewLimit')
            ->with($user)
            ->once()
            ->andReturn(3);

        $this->crewTrainigRepository->shouldReceive('getCountByUser')
            ->with($user)
            ->once()
            ->andReturn(2);

        $this->shipCrewRepository->shouldReceive('getAmountByUser')
            ->with($user)
            ->once()
            ->andReturn(1);

        static::assertSame(
            0,
            $this->subject->getRemainingCount($user)
        );
    }

    public function testGetRemainingCountReturnsValueIfPositive(): void
    {
        $user = $this->mock(User::class);

        $this->crewLimitCalculator->shouldReceive('getGlobalCrewLimit')
            ->with($user)
            ->once()
            ->andReturn(3);

        $this->crewTrainigRepository->shouldReceive('getCountByUser')
            ->with($user)
            ->once()
            ->andReturn(2);

        $this->shipCrewRepository->shouldReceive('getAmountByUser')
            ->with($user)
            ->once()
            ->andReturn(0);

        static::assertSame(
            1,
            $this->subject->getRemainingCount($user)
        );
    }

    public function testGetAssignedCountReturnsValue(): void
    {
        $user = $this->mock(User::class);

        $value = 666;

        $this->shipCrewRepository->shouldReceive('getAmountByUser')
            ->with($user)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getAssignedCount($user)
        );
    }

    public function testGetTrainableCountReturnsValue(): void
    {
        $user = $this->mock(User::class);

        $this->crewLimitCalculator->shouldReceive('getGlobalCrewLimit')
            ->with($user)
            ->once()
            ->andReturn(130);

        static::assertSame(
            13,
            $this->subject->getTrainableCount($user)
        );
    }
}
