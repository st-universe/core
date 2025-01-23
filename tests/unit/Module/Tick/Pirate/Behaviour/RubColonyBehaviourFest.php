<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Behaviour;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\DistanceCalculationInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class RubColonyBehaviourFest extends StuTestCase
{
    /** @var MockInterface&ColonyRepositoryInterface */
    private $colonyRepository;
    /** @var MockInterface&DistanceCalculationInterface */
    private $distanceCalculation;

    /** @var MockInterface&FleetWrapperInterface */
    private $fleetWrapper;

    private PirateBehaviourInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->distanceCalculation = $this->mock(DistanceCalculationInterface::class);

        $this->fleetWrapper = $this->mock(FleetWrapperInterface::class);

        /**
        $this->subject = new RubColonyBehaviour(
            $this->colonyRepository,
            $this->distanceCalculation,
            $this->shipMover
        ); */
    }

    public function actionExpectNothingWhenNoTargetsFound(): void
    {
        $leadShip = $this->mock(ShipInterface::class);

        $this->fleetWrapper->shouldReceive('get->getLeadShip')
            ->withNoArgs()
            ->once()
            ->andReturn($leadShip);

        $this->colonyRepository->shouldReceive('getPirateTargets')
            ->with($leadShip)
            ->once()
            ->andReturn([]);

        $this->subject->action($this->fleetWrapper);
    }

    public function actionExpectFoo(): void
    {
        $leadShip = $this->mock(ShipInterface::class);
        $colonyA = $this->mock(ColonyInterface::class);
        $colonyB = $this->mock(ColonyInterface::class);

        $this->fleetWrapper->shouldReceive('get->getLeadShip')
            ->withNoArgs()
            ->once()
            ->andReturn($leadShip);

        $this->colonyRepository->shouldReceive('getPirateTargets')
            ->with($leadShip)
            ->once()
            ->andReturn([$colonyB, $colonyA]);

        $this->distanceCalculation->shouldReceive('spacecraftToColonyDistance')
            ->with($leadShip, $colonyA)
            ->once()
            ->andReturn(3);
        $this->distanceCalculation->shouldReceive('spacecraftToColonyDistance')
            ->with($leadShip, $colonyB)
            ->once()
            ->andReturn(10);

        $this->subject->action($this->fleetWrapper);
    }
}
