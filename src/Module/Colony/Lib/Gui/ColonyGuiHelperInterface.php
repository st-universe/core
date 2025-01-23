<?php

namespace Stu\Module\Colony\Lib\Gui;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\GameControllerInterface;

interface ColonyGuiHelperInterface
{
    public function registerMenuComponents(
        ColonyMenuEnum $menu,
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void;

    /** @param array<ColonyComponentEnum> $guiComponents */
    public function registerComponents(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game,
        array $guiComponents
    ): void;
}
