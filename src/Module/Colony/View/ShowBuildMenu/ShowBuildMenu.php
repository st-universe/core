<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowBuildMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyGuiHelperInterface $colonyGuiHelper,
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_BUILD, $host, $game);

        $game->showMacro(ColonyMenuEnum::MENU_BUILD->getTemplate());
    }
}
