<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use RuntimeException;
use Stu\Module\Colony\View\ShowModuleFab\ModuleFabricationListItem;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;

final class ModuleFabProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository, private BuildingFunctionRepositoryInterface $buildingFunctionRepository, private ModuleQueueRepositoryInterface $moduleQueueRepository) {}

    /** @param Colony $entity */
    #[\Override]
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user
    ): void {

        $func = $this->buildingFunctionRepository->find(request::getIntFatal('func'));
        if ($func === null) {
            throw new RuntimeException('parameter func is missing');
        }

        $modules = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            $func->getFunction(),
            $user->getId()
        );

        $list = [];
        foreach ($modules as $module) {
            $list[] = new ModuleFabricationListItem(
                $this->moduleQueueRepository,
                $module->getModule(),
                $entity
            );
        }

        $template->setTemplateVar('FUNC', $func);
        $template->setTemplateVar('MODULE_LIST', $list);
    }
}
