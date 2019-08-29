<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use BuildingFunctions;
use ColonyMenu;
use ModuleBuildingFunction;
use Modules;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    private $colonyLoader;

    private $showModuleFabRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleFabRequestInterface $showModuleFabRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleFabRequest = $showModuleFabRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleFabRequest->getColonyId(),
            $userId
        );

        $func = new BuildingFunctions(request::getIntFatal('func'));
        $modules = ModuleBuildingFunction::getByFunctionAndUser($func->getFunction(), $userId);

        $list = [];
        foreach ($modules as $module) {
            $list[] = new ModuleFabricationListItemTal(
                new Modules($module->getModuleId()),
                $colony
            );
        }

        $game->showMacro('html/colonymacros.xhtml/cm_modulefab');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_MODULEFAB));
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('MODULE_LIST', $list);
    }
}
