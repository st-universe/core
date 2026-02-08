<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Monolog\Logger;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Logging\StuLogger;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Location;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\StuTestCase;

class IonStormMovementTest extends StuTestCase
{
    private MockInterface&AnomalyRepositoryInterface $anomalyRepository;
    private MockInterface&LocationRepositoryInterface $locationRepository;
    private MockInterface&StuRandom $stuRandom;

    private IonStormMovement $subject;

    #[\Override]
    protected function setUp(): void
    {
        StuLogger::setMock(new Logger('test'));

        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->locationRepository = $this->mock(LocationRepositoryInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new IonStormMovement(
            $this->anomalyRepository,
            $this->locationRepository,
            $this->stuRandom
        );
    }

    public function testMoveStormExpectMovementChangeWhenTypeVariable(): void
    {
        $root = $this->mock(Anomaly::class);
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
        $root = $this->mock(Anomaly::class);
        $child = $this->mock(Anomaly::class);
        $child2 = $this->mock(Anomaly::class);
        $childOnBorder = $this->mock(Anomaly::class);
        $locationPool = $this->mock(LocationPool::class);
        $childLocation = $this->mock(Location::class);
        $child2Location = $this->mock(Location::class);
        $newLocation = $this->mock(Location::class);
        $childOnBorderLocation = $this->mock(Location::class);
        $locationWithIonStorm = $this->mock(Location::class);

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
        $childLocation->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');
        $childLocation->shouldReceive('getAnomalies->getValues')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $childOnBorderLocation->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $childOnBorderLocation->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(4);
        $childOnBorderLocation->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR_BORDER_CHILD');
        $child2Location->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $child2Location->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn(6);
        $child2Location->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR_CHILD2');

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
        $newLocation->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('NEW_SECTOR');
        $newLocation->shouldReceive('getAnomalies->getValues')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $locationWithIonStorm->shouldReceive('hasAnomaly')
            ->with(AnomalyTypeEnum::ION_STORM)
            ->once()
            ->andReturn(true);

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

        $this->locationRepository->shouldReceive('save')
            ->with($childLocation)
            ->once();
        $this->locationRepository->shouldReceive('save')
            ->with($newLocation)
            ->once();

        $this->subject->moveStorm($root, $ionStormData, $locationPool);
    }

    public function testMoveStormExpectDeletionWhenTargetLocationForbidden(): void
    {
        $root = $this->mock(Anomaly::class);
        $childWithForbiddenTarget = $this->mock(Anomaly::class);
        $childWithForbiddenTargetLocation = $this->mock(Location::class);
        $forbiddenLocation = $this->mock(Location::class);
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
        $childWithForbiddenTargetLocation->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

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
