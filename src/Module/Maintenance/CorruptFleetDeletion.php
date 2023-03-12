<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class CorruptFleetDeletion implements MaintenanceHandlerInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(): void
    {
        foreach ($this->fleetRepository->getNonNpcFleetList() as $fleet) {
            if (
                $fleet->getCrewSum() > GameEnum::CREW_PER_FLEET
                || $fleet->getShipCount() == 0
            ) {
                foreach ($fleet->getShips() as $fleetShip) {
                    $fleetShip->setFleet(null);
                    $fleetShip->setIsFleetLeader(false);

                    $this->shipRepository->save($fleetShip);
                }

                $this->fleetRepository->delete($fleet);
            }
        }
    }
}
