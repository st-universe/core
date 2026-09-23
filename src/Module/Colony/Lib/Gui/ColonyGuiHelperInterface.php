<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\Component\View\ViewControllerContext;

interface ColonyGuiHelperInterface
{
    public function registerMenuComponents(
        ColonyMenuEnum $menu,
        PlanetFieldHostInterface $host,
        ViewControllerContext $game
    ): void;

    /** @param array<ColonyComponentEnum> $guiComponents */
    public function registerComponents(
        PlanetFieldHostInterface $host,
        ViewControllerContext $game,
        array $guiComponents
    ): void;
}
