<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowEpsBar implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_EPSBAR_AJAX';

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

        $this->colonyGuiHelper->registerComponents($host, $game, [GuiComponentEnum::EPS_BAR, GuiComponentEnum::SURFACE]);

        $game->showMacro('html/colony/component/colonyEps.twig');
    }
}
