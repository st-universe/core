<?php

namespace Stu\Module\Colony\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Control\GameControllerInterface;

interface ColonyGuiHelperInterface
{
    /** @param array<GuiComponentEnum> $whitelist */
    public function registerComponents(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
        array $whitelist = null
    ): void;
}
