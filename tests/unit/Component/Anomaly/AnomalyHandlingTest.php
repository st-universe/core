<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyHandlerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuTestCase;

class AnomalyHandlingTest extends StuTestCase
{
    /** @var MockInterface&AnomalyRepositoryInterface */
    private $anomalyRepository;

    /** @var MockInterface&AnomalyHandlerInterface */
    private $handler;

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

        $this->anomalyRepository->shouldReceive('findAllRoot')
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
        $anomaly->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([]));

        $this->anomalyRepository->shouldReceive('findAllRoot')
            ->withNoArgs()
            ->once()
            ->andReturn([$anomaly]);
        $this->anomalyRepository->shouldReceive('delete')
            ->with($anomaly)
            ->once();
        $this->handler->shouldReceive('handleSpacecraftTick')
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
        $child = $this->mock(AnomalyInterface::class);

        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(42);
        $anomaly->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $anomaly->shouldReceive('changeRemainingTicks')
            ->with(-1)
            ->once();
        $anomaly->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$child]));

        $child->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(17);
        $child->shouldReceive('changeRemainingTicks')
            ->with(-1)
            ->once();
        $child->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([]));

        $this->anomalyRepository->shouldReceive('findAllRoot')
            ->withNoArgs()
            ->once()
            ->andReturn([$anomaly]);
        $this->anomalyRepository->shouldReceive('save')
            ->with($anomaly)
            ->once();
        $this->anomalyRepository->shouldReceive('save')
            ->with($child)
            ->once();

        $this->handler->shouldReceive('handleSpacecraftTick')
            ->with($anomaly)
            ->once();

        $this->subject->processExistingAnomalies();
    }

    public function testHandleIncomingSpacecraft(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $location = $this->mock(MapInterface::class);
        $anomaly1 = $this->mock(AnomalyInterface::class);
        $anomaly2 = $this->mock(AnomalyInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);

        $anomaly1->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(42);
        $anomaly2->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->andReturn(42);

        $wrapper->shouldReceive('get->getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);
        $location->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$anomaly1, $anomaly2]));

        $this->handler->shouldReceive('handleIncomingSpacecraft')
            ->with($wrapper, $anomaly1, $messages)
            ->once();
        $this->handler->shouldReceive('handleIncomingSpacecraft')
            ->with($wrapper, $anomaly2, $messages)
            ->once();

        $this->subject->handleIncomingSpacecraft($wrapper, $messages);
    }
}
