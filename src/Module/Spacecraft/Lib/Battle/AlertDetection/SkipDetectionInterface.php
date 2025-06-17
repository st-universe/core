<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface SkipDetectionInterface
{
    /** @param Collection<int, UserInterface> $usersToInformAboutTrojanHorse */
    public function isSkipped(
        SpacecraftInterface $incomingSpacecraft,
        SpacecraftWrapperInterface $alertedWrapper,
        ?SpacecraftInterface $tractoringSpacecraft,
        Collection $usersToInformAboutTrojanHorse
    ): bool;
}
