<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface TrojanHorseNotifierInterface
{
    /**
     * @param Collection<int, User> $users
     */
    public function informUsersAboutTrojanHorse(
        Spacecraft $incomingSpacecraft,
        ?Spacecraft $tractoringSpacecraft,
        Collection $users
    ): void;
}
