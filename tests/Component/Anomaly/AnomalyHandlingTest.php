<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Override;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuTestCase;

class AnomalyHandlingTest extends StuTestCase
{
    /** @var MockInterface&AnomalyRepositoryInterface */
    private MockInterface $anomalyRepository;

    private AnomalyHandlerInterface $handler;

    private AnomalyHandlingInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);

        $this->handler = $this->mock(AnomalyHandlerInterface::class);

        $this->subject = new AnomalyHandling(
            $this->anomalyRepository,
            [42 => $this->handler]
        );
    }

    public function testProcessExistingAnomaliesExpectExceptionWhenUnknownAnomalyType(): void
    {
        static::expectExceptionMessage('no handler defined for type: 666');
        static::expectException(RuntimeException::class);

        $anomaly = $this->mock(AnomalyInterface::class);

        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(666);

        $this->anomalyRepository->shouldReceive('findAllActive')
            ->withNoArgs()
            ->once()
            ->andReturn([$anomaly]);

        $this->subject->processExistingAnomalies();
    }

    public function testProcessExistingAnomaliesExpectDisappearWhenLifespanIsOver(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);

        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(42);
        $anomaly->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(0)
            ->once();

        $this->anomalyRepository->shouldReceive('findAllActive')
            ->withNoArgs()
            ->once()
            ->andReturn([$anomaly]);
        $this->anomalyRepository->shouldReceive('save')
            ->with($anomaly)
            ->once();
        $this->handler->shouldReceive('handleShipTick')
            ->with($anomaly)
            ->once();
        $this->handler->shouldReceive('letAnomalyDisappear')
            ->with($anomaly)
            ->once();

        $this->subject->processExistingAnomalies();
    }

    public function testProcessExistingAnomaliesExpectReductionIfStillAlive(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);

        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(42);
        $anomaly->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(1)
            ->once();

        $this->anomalyRepository->shouldReceive('findAllActive')
            ->withNoArgs()
            ->once()
            ->andReturn([$anomaly]);
        $this->anomalyRepository->shouldReceive('save')
            ->with($anomaly)
            ->once();
        $this->handler->shouldReceive('handleShipTick')
            ->with($anomaly)
            ->once();

        $this->subject->processExistingAnomalies();
    }
}
