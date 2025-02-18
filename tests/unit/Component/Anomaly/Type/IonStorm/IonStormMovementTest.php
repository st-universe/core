<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuTestCase;

class IonStormMovementTest extends StuTestCase
{
    /** @var MockInterface&AnomalyRepositoryInterface */
    private $anomalyRepository;
    /** @var MockInterface&StuRandom */
    private $stuRandom;

    private IonStormMovement $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new IonStormMovement(
            $this->anomalyRepository,
            $this->stuRandom
        );
    }

    public function testMoveStormExpectMovementChangeWhenTypeVariable(): void
    {
        $root = $this->mock(AnomalyInterface::class);
        $locationPool = $this->mock(LocationPool::class);

        $ionStormData = new IonStormData(0, 0, IonStormMovementType::VARIABLE);

        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $root->shouldReceive('setData')
            ->with('{"directionInDegrees":180,"velocity":3,"movementType":2}')
            ->once();

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 360)
            ->once()
            ->andReturn(180);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 5, true, 2)
            ->once()
            ->andReturn(3);

        $this->anomalyRepository->shouldReceive('save')
            ->with($root)
            ->once();

        $this->subject->moveStorm($root, $ionStormData, $locationPool);
    }

    public function testMoveStormExpectMovementOfChildren(): void
    {
        $root = $this->mock(AnomalyInterface::class);
        $child = $this->mock(AnomalyInterface::class);
        $child2 = $this->mock(AnomalyInterface::class);
        $childOnBorder = $this->mock(AnomalyInterface::class);
        $locationPool = $this->mock(LocationPool::class);
        $childLocation = $this->mock(LocationInterface::class);
        $child2Location = $this->mock(LocationInterface::class);
        $newLocation = $this->mock(LocationInterface::class);
        $childOnBorderLocation = $this->mock(LocationInterface::class);
        $locationWithIonStorm = $this->mock(LocationInterface::class);

        $ionStormData = new IonStormData(45, 4, IonStormMovementType::STATIC);

        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $child,
                $childOnBorder,
                $child2
            ]));

        $child->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($childLocation);
        $child2->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($child2Location);
        $childOnBorder->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($childOnBorderLocation);

        $childLocation->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $childLocation->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $childOnBorderLocation->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $childOnBorderLocation->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(4);
        $child2Location->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $child2Location->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(6);

        $locationPool->shouldReceive('getLocation')
            ->with(4, 5)
            ->once()
            ->andReturn($newLocation);
        $locationPool->shouldReceive('getLocation')
            ->with(6, 7)
            ->once()
            ->andReturn(null);
        $locationPool->shouldReceive('getLocation')
            ->with(8, 9)
            ->once()
            ->andReturn($locationWithIonStorm);

        $newLocation->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(false);
        $newLocation->shouldReceive('isAnomalyForbidden')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $newLocation->shouldReceive('addAnomaly')
            ->with($child)
            ->once();
        $locationWithIonStorm->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(true);

        $childLocation->shouldReceive('getAnomalies->removeElement')
            ->with($child)
            ->once();
        $child->shouldReceive('setLocation')
            ->with($newLocation)
            ->once();

        $this->anomalyRepository->shouldReceive('save')
            ->with($child)
            ->once();
        $this->anomalyRepository->shouldReceive('delete')
            ->with($child2)
            ->once();
        $this->anomalyRepository->shouldReceive('delete')
            ->with($childOnBorder)
            ->once();

        $this->subject->moveStorm($root, $ionStormData, $locationPool);
    }

    public function testMoveStormExpectDeletionWhenTargetLocationForbidden(): void
    {
        $root = $this->mock(AnomalyInterface::class);
        $childWithForbiddenTarget = $this->mock(AnomalyInterface::class);
        $childWithForbiddenTargetLocation = $this->mock(LocationInterface::class);
        $forbiddenLocation = $this->mock(LocationInterface::class);
        $locationPool = $this->mock(LocationPool::class);

        $ionStormData = new IonStormData(45, 4, IonStormMovementType::STATIC);

        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $childWithForbiddenTarget
            ]));

        $childWithForbiddenTarget->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($childWithForbiddenTargetLocation);

        $childWithForbiddenTargetLocation->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $childWithForbiddenTargetLocation->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(8);

        $locationPool->shouldReceive('getLocation')
            ->with(10, 11)
            ->once()
            ->andReturn($forbiddenLocation);

        $forbiddenLocation->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(false);
        $forbiddenLocation->shouldReceive('isAnomalyForbidden')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->anomalyRepository->shouldReceive('delete')
            ->with($childWithForbiddenTarget)
            ->once();

        $this->subject->moveStorm($root, $ionStormData, $locationPool);
    }
}
