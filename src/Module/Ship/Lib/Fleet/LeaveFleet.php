<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Override;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveFleet implements LeaveFleetInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private ChangeFleetLeaderInterface $changeFleetLeader)
    {
    }

    #[Override]
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
