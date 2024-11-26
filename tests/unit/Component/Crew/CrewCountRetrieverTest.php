<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Mockery\MockInterface;
use Override;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\StuTestCase;

class CrewCountRetrieverTest extends StuTestCase
{
    /** @var MockInterface&CrewRepositoryInterface */
    private MockInterface $crewRepository;

    /** @var MockInterface&ShipCrewRepositoryInterface */
    private MockInterface $shipCrewRepository;

    /** @var MockInterface&CrewTrainingRepositoryInterface */
    private MockInterface $crewTrainigRepository;

    /** @var MockInterface&CrewLimitCalculatorInterface */
    private MockInterface $crewLimitCalculator;

    private CrewCountRetriever $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->shipCrewRepository = $this->mock(ShipCrewRepositoryInterface::class);
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
        $user = $this->mock(UserInterface::class);

        $amount_tradeposts = 666;
        $amount_debris = 42;

        $this->crewRepository->shouldReceive('getAmountByUserAndShipRumpCategory')
            ->with($user, ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS)
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

        $user = $this->mock(UserInterface::class);

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
        $user = $this->mock(UserInterface::class);

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
        $user = $this->mock(UserInterface::class);

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
        $user = $this->mock(UserInterface::class);

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
        $user = $this->mock(UserInterface::class);

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
        $user = $this->mock(UserInterface::class);

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
