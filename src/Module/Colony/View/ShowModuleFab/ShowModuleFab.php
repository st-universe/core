<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use ColonyMenu;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    private $colonyLoader;

    private $showModuleFabRequest;

    private $moduleBuildingFunctionRepository;

    private $buildingFunctionRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleFabRequestInterface $showModuleFabRequest,
        ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleFabRequest = $showModuleFabRequest;
        $this->moduleBuildingFunctionRepository = $moduleBuildingFunctionRepository;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleFabRequest->getColonyId(),
            $userId
        );

        $func = $this->buildingFunctionRepository->find((int) request::getIntFatal('func'));
        $modules = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            (int) $func->getFunction(),
            $userId
        );

        $list = [];
        foreach ($modules as $module) {
            $list[] = new ModuleFabricationListItemTal(
                $module->getModule(),
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
