<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveFleet implements LeaveFleetInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ChangeFleetLeaderInterface $changeFleetLeader;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ChangeFleetLeaderInterface $changeFleetLeader
    ) {
        $this->shipRepository = $shipRepository;
        $this->changeFleetLeader = $changeFleetLeader;
    }

    public function leaveFleet(ShipInterface $ship): bool
    {
        $fleet = $ship->getFleet();

        if ($fleet === null) {
            return false;
        }

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader->change($ship);
        } else {
            $fleet->getShips()->removeElement($ship);

            $ship->setFleet(null);
            $ship->setIsFleetLeader(false);
            $ship->setFleetId(null);
        }

        $this->shipRepository->save($ship);

        return true;
    }
}
