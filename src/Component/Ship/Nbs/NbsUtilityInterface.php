<?php

namespace Stu\Component\Ship\Nbs;

use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;

interface NbsUtilityInterface
{
    public function isTachyonActive(ShipInterface $ship): bool;

    public function setNbsTemplateVars(
        ShipInterface $ship,
        GameControllerInterface $game,
        ?SessionInterface $session,
        bool $tachyonActive,
        int $mapId = null,
        int $sysMapId = null
    ): void;
}
