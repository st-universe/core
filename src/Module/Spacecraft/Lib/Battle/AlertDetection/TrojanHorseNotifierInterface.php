<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface TrojanHorseNotifierInterface
{
    /**
     * @param Collection<int, UserInterface> $users
     */
    public function informUsersAboutTrojanHorse(
        SpacecraftInterface $incomingSpacecraft,
        ?SpacecraftInterface $tractoringSpacecraft,
        Collection $users
    ): void;
}
