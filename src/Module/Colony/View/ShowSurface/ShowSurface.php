<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSurface;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowSurface implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SURFACE';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private ColonyGuiHelperInterface $colonyGuiHelper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $this->colonyGuiHelper->registerComponents($host, $game, [GuiComponentEnum::SURFACE]);

        $game->showMacro('html/colony/component/colonySurface.twig');
    }
}
