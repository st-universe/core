<?php

namespace Stu\Module\Colony\Lib\Gui;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;

interface ColonyGuiHelperInterface
{
    public function registerComponents(
        ColonyMenuEnum $menu,
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void;
}
