<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Mockery;
use Mockery\MockInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ChangeFleetLeaderTest extends StuTestCase
{
    /** @var MockInterface|FleetRepositoryInterface */
    private MockInterface $fleetRepository;

    /** @var MockInterface|ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface|CancelColonyBlockOrDefendInterface */
    private MockInterface $cancelColonyBlockOrDefend;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $ship;

    private ChangeFleetLeaderInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->fleetRepository = $this->mock(FleetRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->cancelColonyBlockOrDefend = $this->mock(CancelColonyBlockOrDefendInterface::class);

        //params
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new ChangeFleetLeader(
            $this->fleetRepository,
            $this->shipRepository,
            $this->cancelColonyBlockOrDefend
        );
    }

    public function testChangeExpectFleetDeletionIfSingleShip(): void
    {
        $fleet = $this->mock(FleetInterface::class);
        $fleetShips = [$this->ship];

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

        $fleet->shouldReceive('getShips->toArray')
            ->withNoArgs()
            ->once()
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
    }

    public function testChangeExpectLeaderChangeIfNotSingleShip(): void
    {
        $fleet = $this->mock(FleetInterface::class);
        $otherShip = $this->mock(ShipInterface::class);
        $fleetShips = [$this->ship, $otherShip];

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

        $this->subject->change($this->ship);
    }
}
