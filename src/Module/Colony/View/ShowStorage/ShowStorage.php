<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowStorage;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\Gui\GuiComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowStorage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_STORAGE_AJAX';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private ColonyGuiHelperInterface $colonyGuiHelper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $this->colonyGuiHelper->registerComponents($host, $game, [GuiComponentEnum::STORAGE]);

        $game->showMacro('html/colony/component/colonyStorage.twig');
    }
}
