<?php

namespace Stu\Module\Colony\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;

interface ColonyGuiHelperInterface
{
    public function getColonyMenu(int $menuId): string;

    public function register(ColonyInterface $colony, GameControllerInterface $game): void;
}
