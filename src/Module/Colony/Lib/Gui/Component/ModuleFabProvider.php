<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use RuntimeException;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\View\ShowModuleFab\ModuleFabricationListItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabProvider implements GuiComponentProviderInterface
{
    public function __construct(private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository, private BuildingFunctionRepositoryInterface $buildingFunctionRepository, private ModuleQueueRepositoryInterface $moduleQueueRepository)
    {
    }

    /** @param ColonyInterface $host */
    #[Override]
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
            $list[] = new ModuleFabricationListItem(
                $this->moduleQueueRepository,
                $module->getModule(),
                $host
            );
        }

        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('MODULE_LIST', $list);
    }
}
