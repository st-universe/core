<?php

namespace Stu\Component\Spacecraft\Nbs;

use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;

interface NbsUtilityInterface
{
    public function isTachyonActive(Spacecraft $spacecraft): bool;

    public function setNbsTemplateVars(
        Spacecraft $spacecraft,
        GameControllerInterface $game,
        ?SessionStorageInterface $sessionStorage,
        bool $tachyonActive,
        Map|StarSystemMap|null $field = null
    ): void;
}
