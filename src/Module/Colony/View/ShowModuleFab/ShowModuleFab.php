<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use InvalidArgumentException;
use Override;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpacecraftRump;
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

        $template = match ($func->getFunction()) {
            BuildingFunctionEnum::FABRICATION_HALL => ColonyMenuEnum::MENU_FAB_HALL->getTemplate(),
            BuildingFunctionEnum::TECH_CENTER => ColonyMenuEnum::MENU_TECH_CENTER->getTemplate(),
            default => ColonyMenuEnum::MENU_MODULEFAB->getTemplate(),
        };

        /** @var array<int, ModuleFabricationListItem> $allModules */
        $allModules = [];

        $rumps = $this->spacecraftRumpRepository->getBuildableByUser($userId);

        $this->setModules($colony, $func, $game, $allModules);
        $this->setRumpModules($colony, $rumps, $allModules);
        $this->setModuleTypes($game);
        $this->setBuildplans($rumps, $allModules, $game);

        $game->showMacro($template);

        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_MODULEFAB);
        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('SHIP_RUMPS', $rumps);

        $game->addExecuteJS('clearModuleInputs();', JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    private function setModuleTypes(GameControllerInterface $game): void
    {
        $moduleTypes = [];
        foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {
            $moduleTypes[$moduleType->value] = [
                'name' => $moduleType->getDescription(),
                'image' => "/assets/buttons/modul_screen_{$moduleType->value}.png"
            ];
        }

        $game->setTemplateVar('MODULE_TYPES', $moduleTypes);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     */
    private function setBuildplans(array $rumps, array &$allModules, GameControllerInterface $game): void
    {
        $buildplans = [];
        foreach ($rumps as $rump) {
            $rumpId = $rump->getId();
            $rumpBuildplans = $this->spacecraftBuildplanRepository->getByUserAndRump($game->getUser()->getId(), $rumpId);
            $buildplans[$rumpId] = $rumpBuildplans;

            foreach ($rumpBuildplans as $buildplan) {

                foreach ($buildplan->getModules() as $buildplanModule) {
                    $moduleId = $buildplanModule->getModule()->getId();

                    if (array_key_exists($moduleId, $allModules)) {
                        $allModules[$moduleId]->addBuildplan($buildplan);
                    }
                }
            }
        }

        $game->setTemplateVar('BUILDPLANS', $buildplans);
    }

    /**
     * @param array<int, ModuleFabricationListItem> $allModules
     */
    private function setModules(Colony $colony, BuildingFunction $func, GameControllerInterface $game, array &$allModules): void
    {
        /** @var array<int, array<int, array<int, ModuleFabricationListItem>>> $sortedModules */
        $sortedModules = [];

        $moduleBuildingFunctions = $this->moduleBuildingFunctionRepository->getByBuildingFunctionAndUser(
            $func->getFunction(),
            $game->getUser()->getId()
        );

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

        $game->setTemplateVar('MODULES_BY_TYPE_AND_LEVEL', $sortedModules);
    }

    /**
     * @param array<SpacecraftRump> $rumps
     * @param array<int, ModuleFabricationListItem> $allModules
     */
    private function setRumpModules(Colony $colony, array $rumps, array &$allModules): void
    {
        foreach ($rumps as $rump) {
            $rumpId = $rump->getId();
            $rumpRoleId = $rump->getRoleId();

            $shipRumpModuleLevel = $this->shipRumpModuleLevelRepository->getByShipRump($rump);
            if ($shipRumpModuleLevel === null) {
                throw new InvalidArgumentException('this should not happen');
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
    }
}
