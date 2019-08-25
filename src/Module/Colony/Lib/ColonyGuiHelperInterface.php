<?php

namespace Stu\Module\Colony\Lib;

use Stu\Control\GameControllerInterface;

interface ColonyGuiHelperInterface
{
    public function getColonyMenu(int $menuId): string;

    public function register(\Colony $colony, GameControllerInterface $game);
}