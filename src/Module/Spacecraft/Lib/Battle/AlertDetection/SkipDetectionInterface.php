<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface SkipDetectionInterface
{
    /** @param Collection<int, UserInterface> $usersToInformAboutTrojanHorse */
    public function isSkipped(
        SpacecraftInterface $incomingSpacecraft,
        SpacecraftInterface $alertedSpacecraft,
        ?SpacecraftInterface $tractoringSpacecraft,
        Collection $usersToInformAboutTrojanHorse
    ): bool;
}
