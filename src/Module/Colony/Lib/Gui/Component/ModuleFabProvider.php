<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use RuntimeException;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\View\ShowModuleFab\ModuleFabricationListItemTal;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabProvider implements GuiComponentProviderInterface
{
    private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    public function __construct(
        ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ModuleQueueRepositoryInterface $moduleQueueRepository
    ) {
        $this->moduleBuildingFunctionRepository = $moduleBuildingFunctionRepository;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->moduleQueueRepository = $moduleQueueRepository;
    }

    /** @param ColonyInterface $host */
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {

        $func = $this->buildingFunctionRepository->find(request::getIntFatal('func'));
        if ($func === null) {
            throw new RuntimeException('parameter func is missing');
        }

        $modules = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            $func->getFunction(),
            $game->getUser()->getId()
        );

        $list = [];
        foreach ($modules as $module) {
            $list[] = new ModuleFabricationListItemTal(
                $this->moduleQueueRepository,
                $module->getModule(),
                $host
            );
        }

        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('MODULE_LIST', $list);
    }
}
