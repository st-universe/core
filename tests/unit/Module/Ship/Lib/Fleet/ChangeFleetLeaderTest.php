<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ChangeFleetLeaderTest extends StuTestCase
{
    /** @var MockInterface&FleetRepositoryInterface */
    private $fleetRepository;
    /** @var MockInterface&ShipRepositoryInterface */
    private $shipRepository;
    /** @var MockInterface&CancelColonyBlockOrDefendInterface */
    private $cancelColonyBlockOrDefend;
    /** @var MockInterface&EntityManagerInterface */
    private $entityManager;

    /** @var MockInterface&ShipInterface */
    private $ship;

    private ChangeFleetLeaderInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->fleetRepository = $this->mock(FleetRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->cancelColonyBlockOrDefend = $this->mock(CancelColonyBlockOrDefendInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        //params
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new ChangeFleetLeader(
            $this->fleetRepository,
            $this->shipRepository,
            $this->cancelColonyBlockOrDefend,
            $this->entityManager,
            $this->initLoggerUtil()
        );
    }

    public function testChangeExpectFleetDeletionIfSingleShip(): void
    {
        $fleet = $this->mock(FleetInterface::class);
        $fleetShips = new ArrayCollection([$this->ship]);

        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);
        $this->ship->shouldReceive('setFleet')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('setIsFleetLeader')
            ->with(false)
            ->once();

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);
        $fleet->shouldReceive('getShips')
            ->withNoArgs()
            ->andReturn($fleetShips);

        $this->cancelColonyBlockOrDefend->shouldReceive('work')
            ->with($this->ship, Mockery::type(InformationWrapper::class))
            ->once();

        $this->fleetRepository->shouldReceive('delete')
            ->with($fleet)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();

        $this->subject->change($this->ship);

        $this->assertTrue($fleetShips->isEmpty());
    }

    public function testChangeExpectLeaderChangeIfNotSingleShip(): void
    {
        $fleet = $this->mock(FleetInterface::class);
        $otherShip = $this->mock(ShipInterface::class);
        $fleetShips = [$this->ship, $otherShip];

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);
        $this->ship->shouldReceive('getFleet')
            ->withNoArgs()
            ->once()
            ->andReturn($fleet);
        $this->ship->shouldReceive('setFleet')
            ->with(null)
            ->once();
        $this->ship->shouldReceive('setIsFleetLeader')
            ->with(false)
            ->once();

        $otherShip->shouldReceive('setIsFleetLeader')
            ->with(true)
            ->once();
        $otherShip->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(44);

        $fleet->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(43);
        $fleet->shouldReceive('getShips->toArray')
            ->withNoArgs()
            ->once()
            ->andReturn($fleetShips);
        $fleet->shouldReceive('setLeadShip')
            ->with($otherShip)
            ->once();
        $fleet->shouldReceive('getShips->removeElement')
            ->with($this->ship)
            ->once();

        $this->fleetRepository->shouldReceive('save')
            ->with($fleet)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($otherShip)
            ->once();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->change($this->ship);
    }
}
