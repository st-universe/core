<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Anomaly\Type\IonStorm\IonStormData;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\AnomalyTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\AnomalyTypeRepositoryInterface;
use Stu\StuTestCase;

class AnomalyCreationTest extends StuTestCase
{
    private MockInterface&AnomalyRepositoryInterface $anomalyRepository;

    private MockInterface&AnomalyTypeRepositoryInterface $anomalyTypeRepository;

    private AnomalyCreationInterface $subject;

    #[Override]
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

    public function testCreateExpectNewEntityWithMapLocationAndDataObject(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);
        $anomalyType = $this->mock(AnomalyTypeInterface::class);
        $map = $this->mock(MapInterface::class);
        $dataObject = new IonStormData(42, 17);

        $map->shouldReceive('addAnomaly')
            ->with($anomaly)
            ->once();

        $anomalyType->shouldReceive('getLifespanInTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $anomaly->shouldReceive('setAnomalyType')
            ->with($anomalyType)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(123)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setLocation')
            ->with($map)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setData')
            ->with('{"directionInDegrees":42,"velocity":17,"movementType":1}')
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

        $result = $this->subject->create(AnomalyTypeEnum::SUBSPACE_ELLIPSE, $map, null, $dataObject);

        $this->assertSame($anomaly, $result);
    }

    public function testCreateExpectNewEntityWithStarsystemMapLocation(): void
    {
        $anomaly = $this->mock(AnomalyInterface::class);
        $parent = $this->mock(AnomalyInterface::class);
        $anomalyType = $this->mock(AnomalyTypeInterface::class);
        $map = $this->mock(StarSystemMapInterface::class);

        $anomalyType->shouldReceive('getLifespanInTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $map->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123456);
        $map->shouldReceive('addAnomaly')
            ->with($anomaly)
            ->once();

        $parent->shouldReceive('getChildren->set')
            ->with(123456, $anomaly)
            ->once();

        $anomaly->shouldReceive('setAnomalyType')
            ->with($anomalyType)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setRemainingTicks')
            ->with(123)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setLocation')
            ->with($map)
            ->once()
            ->andReturnSelf();
        $anomaly->shouldReceive('setParent')
            ->with($parent)
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

        $result = $this->subject->create(AnomalyTypeEnum::SUBSPACE_ELLIPSE, $map, $parent);

        $this->assertSame($anomaly, $result);
    }
}
