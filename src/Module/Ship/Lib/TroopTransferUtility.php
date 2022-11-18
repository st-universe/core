<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

final class TroopTransferUtility implements TroopTransferUtilityInterface
{
    public function getFreeQuarters(ShipInterface $ship): int
    {
        $free = $ship->getMaxCrewCount() - $ship->getCrewCount();

        return max(0, $free);
    }

    public function getBeamableTroopCount(ShipInterface $ship): int
    {
        $max = $ship->getCrewCount() - $ship->getBuildplan()->getCrew();

        return max(0, $max);
    }

    public function ownCrewOnTarget(UserInterface $user, ShipInterface $ship): int
    {
        $count = 0;

        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() === $user) {
                $count++;
            }
        }

        return $count;
    }

    public function foreignerCount(ShipInterface $ship): int
    {
        $count = 0;

        $user = $ship->getUser();

        foreach ($ship->getCrewlist() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $user) {
                $count++;
            }
        }

        return $count;
    }
}
