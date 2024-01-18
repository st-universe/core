<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    private ColonyLoaderInterface $colonyLoader;

    private ShowModuleFabRequestInterface $showModuleFabRequest;

    private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleFabRequestInterface $showModuleFabRequest,
        ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ModuleQueueRepositoryInterface $moduleQueueRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleFabRequest = $showModuleFabRequest;
        $this->moduleBuildingFunctionRepository = $moduleBuildingFunctionRepository;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->moduleQueueRepository = $moduleQueueRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showModuleFabRequest->getColonyId(),
            $userId,
            false
        );

        $func = $this->buildingFunctionRepository->find(request::getIntFatal('func'));
        $modules = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            $func->getFunction(),
            $userId
        );

        $list = [];
        foreach ($modules as $module) {
            $list[] = new ModuleFabricationListItemTal(
                $this->moduleQueueRepository,
                $module->getModule(),
                $colony
            );
        }

        $game->showMacro(ColonyMenuEnum::MENU_MODULEFAB->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_MODULEFAB);

        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('MODULE_LIST', $list);
    }
}
