<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\AnomalyTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\AnomalyTypeRepositoryInterface;
use Stu\StuTestCase;

class AnomalyCreationTest extends StuTestCase
{
    /** @var MockInterface&AnomalyRepositoryInterface */
    private MockInterface $anomalyRepository;

    /** @var MockInterface&AnomalyTypeRepositoryInterface */
    private MockInterface $anomalyTypeRepository;

    private AnomalyCreationInterface $subject;

    protected function setUp(): void
    {
        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->anomalyTypeRepository = $this->mock(AnomalyTypeRepositoryInterface::class);

        $this->subject = new AnomalyCreation(
            $this->anomalyRepository,
            $this->anomalyTypeRepository
        );
    }

    public function testCreateExpectExceptionWhenAnomalyTypeUnknown(): void
    {
        static::expectExceptionMessage('no anomaly in database for type: 1');
        static::expectException(RuntimeException::class);

        $this->anomalyTypeRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturnNull();

        $this->subject->create(AnomalyTypeEnum::SUBSPACE_ELLIPSE, $this->mock(MapInterface::class));
    }

    public function testCreateExpectNewEntityWithMapLocation(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);
        $anomalyType = $this->mock(AnomalyTypeInterface::class);
        $map = $this->mock(MapInterface::class);

        $anomalyType->shouldReceive('getLifespanInTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $anomaly->shouldReceive('setAnomalyType')
            ->with($anomalyType)
            ->once();
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(123)
            ->once();
        $anomaly->shouldReceive('setMap')
            ->with($map)
            ->once();

        $this->anomalyTypeRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($anomalyType);

        $this->anomalyRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($anomaly);
        $this->anomalyRepository->shouldReceive('save')
            ->with($anomaly)
            ->once();

        $result = $this->subject->create(AnomalyTypeEnum::SUBSPACE_ELLIPSE, $map);

        $this->assertSame($anomaly, $result);
    }

    public function testCreateExpectNewEntityWithStarsystemMapLocation(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);
        $anomalyType = $this->mock(AnomalyTypeInterface::class);
        $map = $this->mock(StarSystemMapInterface::class);

        $anomalyType->shouldReceive('getLifespanInTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $anomaly->shouldReceive('setAnomalyType')
            ->with($anomalyType)
            ->once();
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(123)
            ->once();
        $anomaly->shouldReceive('setStarsystemMap')
            ->with($map)
            ->once();

        $this->anomalyTypeRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($anomalyType);

        $this->anomalyRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($anomaly);
        $this->anomalyRepository->shouldReceive('save')
            ->with($anomaly)
            ->once();

        $result = $this->subject->create(AnomalyTypeEnum::SUBSPACE_ELLIPSE, $map);

        $this->assertSame($anomaly, $result);
    }
}
