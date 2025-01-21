<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuTestCase;

class IonStormPropagationTest extends StuTestCase
{
    /** @var MockInterface&AnomalyRepositoryInterface */
    private $anomalyRepository;
    /** @var MockInterface&AnomalyCreationInterface */
    private $anomalyCreation;
    /** @var MockInterface&StuRandom */
    private $stuRandom;

    private IonStormPropagation $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->anomalyCreation = $this->mock(AnomalyCreationInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new IonStormPropagation(
            $this->anomalyRepository,
            $this->anomalyCreation,
            $this->stuRandom
        );
    }

    public function testPropagateStormExpectDeletionIfChildsEmpty(): void
    {
        $root = $this->mock(AnomalyInterface::class);
        $locationPool = $this->mock(LocationPool::class);

        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $root->shouldReceive('setRemainingTicks')
            ->with(0)
            ->once();

        $this->subject->propagateStorm($root, $locationPool);
    }

    public function testPropagateStorm(): void
    {
        $root = $this->mock(AnomalyInterface::class);
        $newAnomaly = $this->mock(AnomalyInterface::class);
        $locationPool = $this->mock(LocationPool::class);
        $child1 = $this->mock(AnomalyInterface::class);
        $child2WithIonStormOnNeighbour = $this->mock(AnomalyInterface::class);
        $child3LowOnTicks = $this->mock(AnomalyInterface::class);
        $existingIonStorm = $this->mock(AnomalyInterface::class);
        $locationChild1 = $this->mock(LocationInterface::class);
        $locationChild2 = $this->mock(LocationInterface::class);
        $locationWithoutStorm = $this->mock(LocationInterface::class);
        $locationWithIonStorm = $this->mock(LocationInterface::class);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 3)
            ->times(3)
            ->andReturn(3, 1, 2);
        $this->stuRandom->shouldReceive('rand')
            ->with(10, 90)
            ->once()
            ->andReturn(50);
        $this->stuRandom->shouldReceive('rand')
            ->with(20, 70)
            ->once()
            ->andReturn(70);

        $child1->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($locationChild1);
        $child2WithIonStormOnNeighbour->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($locationChild2);

        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([
                $child1,
                $child2WithIonStormOnNeighbour,
                $child3LowOnTicks
            ]));

        $locationPool->shouldReceive('getNeighbours')
            ->with($locationChild1)
            ->once()
            ->andReturn([$locationWithoutStorm]);
        $locationPool->shouldReceive('getNeighbours')
            ->with($locationChild2)
            ->once()
            ->andReturn([$locationWithIonStorm]);

        $locationWithoutStorm->shouldReceive('getAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(null);
        $locationWithIonStorm->shouldReceive('getAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn($existingIonStorm);

        $this->anomalyCreation->shouldReceive('create')
            ->with(AnomalyTypeEnum::ION_STORM, $locationWithoutStorm, $root)
            ->once()
            ->andReturn($newAnomaly);

        $root->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->times(4)
            ->andReturn(34, 33, 33, 33);
        $child1->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->twice()
            ->andReturn(10);
        $child2WithIonStormOnNeighbour->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->twice()
            ->andReturn(15);
        $child3LowOnTicks->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(9);

        $root->shouldReceive('changeRemainingTicks')
            ->with(-1)
            ->once();
        $child1->shouldReceive('changeRemainingTicks')
            ->with(+1)
            ->once();
        $child1->shouldReceive('changeRemainingTicks')
            ->with(-5)
            ->once();
        $newAnomaly->shouldReceive('changeRemainingTicks')
            ->with(+5)
            ->once();
        $child2WithIonStormOnNeighbour->shouldReceive('changeRemainingTicks')
            ->with(-10)
            ->once();
        $existingIonStorm->shouldReceive('changeRemainingTicks')
            ->with(+10)
            ->once();

        $newAnomaly->shouldReceive('setRemainingTicks')
            ->with(0)
            ->once()
            ->andReturnSelf();

        $this->anomalyRepository->shouldReceive('save')
            ->with($root)
            ->times(1);
        $this->anomalyRepository->shouldReceive('save')
            ->with($newAnomaly)
            ->times(2);
        $this->anomalyRepository->shouldReceive('save')
            ->with($existingIonStorm)
            ->times(1);
        $this->anomalyRepository->shouldReceive('save')
            ->with($child1)
            ->times(2);
        $this->anomalyRepository->shouldReceive('save')
            ->with($child2WithIonStormOnNeighbour)
            ->times(1);

        $this->subject->propagateStorm($root, $locationPool);
    }
}
