<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface SkipDetectionInterface
{
    /** @param Collection<int, User> $usersToInformAboutTrojanHorse */
    public function isSkipped(
        Spacecraft $incomingSpacecraft,
        SpacecraftWrapperInterface $alertedWrapper,
        ?Spacecraft $tractoringSpacecraft,
        Collection $usersToInformAboutTrojanHorse
    ): bool;
}
