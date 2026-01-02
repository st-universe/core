<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CorruptFleetDeletion implements MaintenanceHandlerInterface
{
    public function __construct(private FleetRepositoryInterface $fleetRepository)
    {
    }

    #[\Override]
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
                }

                $this->fleetRepository->delete($fleet);
            }
        }
    }
}
