<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use Override;
use request;
use RuntimeException;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Game\GameEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ShowModuleFabRequestInterface $showModuleFabRequest,
        private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        private BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        private ModuleQueueRepositoryInterface $moduleQueueRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ModuleRepositoryInterface $moduleRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showModuleFabRequest->getColonyId(),
            $userId,
            false
        );

        $func = $this->buildingFunctionRepository->find(request::getIntFatal('func'));

        if ($func === null) {
            return;
        }

        $moduleBuildingFunctions = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            $func->getFunction(),
            $userId
        );

        $template = match ($func->getFunction()) {
            BuildingFunctionEnum::FABRICATION_HALL => ColonyMenuEnum::MENU_FAB_HALL->getTemplate(),
            BuildingFunctionEnum::TECH_CENTER => ColonyMenuEnum::MENU_TECH_CENTER->getTemplate(),
            default => ColonyMenuEnum::MENU_MODULEFAB->getTemplate(),
        };

        /** @var array<int, array<int, array<int, ModuleFabricationListItem>>> $sortedModules */
        $sortedModules = [];
        /** @var array<int, ModuleFabricationListItem> $allModules */
        $allModules = [];

        foreach ($moduleBuildingFunctions as $moduleBuildingFunction) {
            $module = $moduleBuildingFunction->getModule();
            $moduleType = $module->getType()->value;
            $moduleLevel = $module->getLevel();
            if (!isset($sortedModules[$moduleType])) {
                $sortedModules[$moduleType] = [];
            }
            if (!isset($sortedModules[$moduleType][$moduleLevel])) {
                $sortedModules[$moduleType][$moduleLevel] = [];
            }

            $moduleFabricationListItem = new ModuleFabricationListItem(
                $this->moduleQueueRepository,
                $module,
                $colony
            );

            $sortedModules[$moduleType][$moduleLevel][] = $moduleFabricationListItem;
            $allModules[$module->getId()] = $moduleFabricationListItem;
        }

        $shipRumps = $this->spacecraftRumpRepository->getBuildableByUser($userId);

        $moduleTypes = [];
        foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {
            $moduleTypes[$moduleType->value] = [
                'name' => $moduleType->getDescription(),
                'image' => "/assets/buttons/modul_screen_{$moduleType->value}.png"
            ];
        }

        foreach ($shipRumps as $rump) {
            $rumpId = $rump->getId();
            $rumpRoleId = $rump->getRoleId();

            $shipRumpModuleLevel = $this->shipRumpModuleLevelRepository->getByShipRump($rump);
            if ($shipRumpModuleLevel === null) {
                throw new RuntimeException('this should not happen');
            }

            foreach ($allModules as $listItem) {
                $module = $listItem->getModule();
                $type = $module->getType();

                if ($type->isSpecialSystemType()) {
                    continue;
                }

                $moduleLevel = $module->getLevel();
                $moduleShipRumpRoleId = $module->getShipRumpRoleId();

                if ($moduleShipRumpRoleId !== null) {
                    if ($moduleShipRumpRoleId === $rumpRoleId) {
                        $listItem->addRump($rump);
                    }
                } else {
                    $min_level = $shipRumpModuleLevel->getMinimumLevel($type);
                    $max_level = $shipRumpModuleLevel->getMaximumLevel($type);

                    if ($moduleLevel >= $min_level && $moduleLevel <= $max_level) {
                        $listItem->addRump($rump);
                    }
                }
            }

            $specialModules = $this->moduleRepository->getBySpecialTypeAndRump(
                $colony,
                SpacecraftModuleTypeEnum::SPECIAL,
                $rumpId
            );
            foreach ($specialModules as $module) {
                if (array_key_exists($module->getId(), $allModules)) {
                    $allModules[$module->getId()]->addRump($rump);
                }
            }
        }

        $buildplans = [];
        foreach ($shipRumps as $rump) {
            $rumpId = $rump->getId();
            $rumpBuildplans = $this->spacecraftBuildplanRepository->getByUserAndRump($userId, $rumpId);
            $buildplans[$rumpId] = $rumpBuildplans;

            foreach ($rumpBuildplans as $buildplan) {

                foreach ($buildplan->getModules() as $buildplanModule) {
                    $moduleType = $buildplanModule->getModuleType()->value;
                    $moduleLevel = $buildplanModule->getModule()->getLevel();
                    $moduleId = $buildplanModule->getModule()->getId();

                    if (array_key_exists($moduleId, $allModules)) {
                        $allModules[$moduleId]->addBuildplan($buildplan);
                    }
                }
            }
        }

        $game->showMacro($template);
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_MODULEFAB);

        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('SHIP_RUMPS', $shipRumps);
        $game->setTemplateVar('MODULE_TYPES', $moduleTypes);
        $game->setTemplateVar('BUILDPLANS', $buildplans);
        $game->setTemplateVar('MODULES_BY_TYPE_AND_LEVEL', $sortedModules);

        $game->addExecuteJS('clearModuleInputs();', GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
