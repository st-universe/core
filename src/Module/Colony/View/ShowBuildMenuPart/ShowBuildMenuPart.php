<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenuPart;

use Building;
use BuildMenuWrapper;
use ColonyMenu;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuildMenuPart implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU_PART';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showBuildMenuPartRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildMenuPartRequestInterface $showBuildMenuPartRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildMenuPartRequest = $showBuildMenuPartRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildMenuPartRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $menus = [];
        $menus[1]['buildings'] = Building::getBuildingMenuList($colony->getId(), 1);
        $menus[2]['buildings'] = Building::getBuildingMenuList($colony->getId(), 2);
        $menus[3]['buildings'] = Building::getBuildingMenuList($colony->getId(), 3);

        $game->showMacro('html/colonymacros.xhtml/buildmenu');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_BUILD));
        $game->setTemplateVar('BUILD_MENUS', $menus);
        $game->setTemplateVar('BUILD_MENU', new BuildMenuWrapper());
    }
}
