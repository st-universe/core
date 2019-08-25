<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

use Building;
use ColonyMenu;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuildMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU';

    private $colonyLoader;

    private $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $menus = [];
        $menus[1]['buildings'] = Building::getBuildingMenuList($colony->getId(), 1);
        $menus[2]['buildings'] = Building::getBuildingMenuList($colony->getId(), 2);
        $menus[3]['buildings'] = Building::getBuildingMenuList($colony->getId(), 3);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_buildmenu');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_BUILD));
        $game->setTemplateVar('BUILD_MENUS', $menus);
        $game->setTemplateVar('BUILD_MENU', new \BuildMenuWrapper());
    }
}
