<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface SkipDetectionInterface
{
    /** @param Collection<int, UserInterface> $usersToInformAboutTrojanHorse */
    public function isSkipped(
        ShipInterface $incomingShip,
        ShipInterface $alertedShip,
        ?ShipInterface $tractoringShip,
        Collection $usersToInformAboutTrojanHorse
    ): bool;
}
