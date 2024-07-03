<?php

namespace Stu\Component\Ship\Nbs;

use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface NbsUtilityInterface
{
    public function isTachyonActive(ShipInterface $ship): bool;

    public function setNbsTemplateVars(
        ShipInterface $ship,
        GameControllerInterface $game,
        ?SessionInterface $session,
        bool $tachyonActive,
        MapInterface|StarSystemMapInterface|null $field = null
    ): void;
}
