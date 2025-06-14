<?php

namespace Stu\Component\Spacecraft\Nbs;

use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface NbsUtilityInterface
{
    public function isTachyonActive(SpacecraftInterface $spacecraft): bool;

    public function setNbsTemplateVars(
        SpacecraftInterface $spacecraft,
        GameControllerInterface $game,
        ?SessionStorageInterface $sessionStorage,
        bool $tachyonActive,
        MapInterface|StarSystemMapInterface|null $field = null
    ): void;
}
