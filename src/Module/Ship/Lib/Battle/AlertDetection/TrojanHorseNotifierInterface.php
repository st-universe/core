<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface TrojanHorseNotifierInterface
{
    /**
     * @param Collection<int, UserInterface> $users
     */
    public function informUsersAboutTrojanHorse(
        ShipInterface $incomingShip,
        ?ShipInterface $tractoringShip,
        Collection $users
    ): void;
}
