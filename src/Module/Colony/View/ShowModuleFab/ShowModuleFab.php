<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use InvalidArgumentException;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCostRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
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
        private ModuleRepositoryInterface $moduleRepository,
        private BuildplanHangarRepositoryInterface $buildplanHangarRepository,
        private ShipRumpCostRepositoryInterface $shipRumpCostRepository
    ) {}

    #[\Override]
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

        $rumps = $this->getModuleFabRumps($userId);
        [$rumps, $exclusiveBuildplansByRump, $exclusiveModulesByRump] = $this->prepareRumps($colony, $rumps);

        $this->setModules($colony, $func, $game, $allModules);
        $this->setRumpModules($colony, $rumps, $allModules, $exclusiveModulesByRump);
        $this->setModuleTypes($game);
        $this->setBuildplans($rumps, $allModules, $game, $exclusiveBuildplansByRump, $exclusiveModulesByRump);

        $game->showMacro($template);

        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_MODULEFAB);
        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('SHIP_RUMPS', $rumps);

        $game->addExecuteJS('clearModuleInputs();', JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    /**
     * @return array<int, SpacecraftRump>
     */
    private function getModuleFabRumps(int $userId): array
    {
        $rumps = $this->spacecraftRumpRepository->getBuildableByUser($userId);

        foreach ($this->spacecraftBuildplanRepository->getStationBuildplansByUser($userId) as $buildplan) {
            $rump = $buildplan->getRump();
            $rumpId = $rump->getId();

            if (!array_key_exists($rumpId, $rumps)) {
                $rumps[$rumpId] = $rump;
            }
        }

        return $rumps;
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
     * @param array<int, SpacecraftBuildplan> $exclusiveBuildplansByRump
     * @param array<int, array<int, array{module: Module, requiredAmount: int}>> $exclusiveModulesByRump
     */
    private function setBuildplans(
        array $rumps,
        array &$allModules,
        GameControllerInterface $game,
        array $exclusiveBuildplansByRump = [],
        array $exclusiveModulesByRump = []
    ): void {
        $buildplans = [];
        foreach ($rumps as $rump) {
            $rumpId = $rump->getId();

            if (array_key_exists($rumpId, $exclusiveBuildplansByRump)) {
                $exclusiveBuildplan = $exclusiveBuildplansByRump[$rumpId];
                $buildplans[$rumpId] = [$exclusiveBuildplan];
                $this->addBuildplanToModules(
                    $exclusiveBuildplan,
                    $exclusiveModulesByRump[$rumpId] ?? [],
                    $allModules
                );
                continue;
            }

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
     * @param array<int, array<int, array{module: Module, requiredAmount: int}>> $exclusiveModulesByRump
     */
    private function setRumpModules(
        Colony $colony,
        array $rumps,
        array &$allModules,
        array $exclusiveModulesByRump = []
    ): void {
        foreach ($rumps as $rump) {
            $rumpId = $rump->getId();

            if (array_key_exists($rumpId, $exclusiveModulesByRump)) {
                $this->addRumpToModules($rump, $exclusiveModulesByRump[$rumpId], $allModules);
                continue;
            }

            $rumpRoleId = $rump->getRoleId();

            if ($rumpRoleId === null) {
                throw new InvalidArgumentException('invalid rump without rump role');
            }

            $shipRumpModuleLevel = $this->shipRumpModuleLevelRepository->getByShipRump($rump);
            if ($shipRumpModuleLevel === null) {
                throw new InvalidArgumentException('this should not happen');
            }

            foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $type) {
                if ($type->isSpecialSystemType()) {
                    continue;
                }

                $minLevel = $shipRumpModuleLevel->getMinimumLevel($type);
                $maxLevel = $shipRumpModuleLevel->getMaximumLevel($type);

                foreach (
                    $this->moduleRepository->getByTypeColonyAndLevel(
                        $colony->getId(),
                        $type,
                        $rumpRoleId,
                        range($minLevel, $maxLevel)
                    ) as $module
                ) {
                    if (array_key_exists($module->getId(), $allModules)) {
                        $allModules[$module->getId()]->addRump($rump);
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

    /**
     * @param array<SpacecraftRump> $rumps
     *
     * @return array{0: array<SpacecraftRump>, 1: array<int, SpacecraftBuildplan>, 2: array<int, array<int, array{module: Module, requiredAmount: int}>>}
     */
    private function prepareRumps(Colony $colony, array $rumps): array
    {
        $validRumps = [];
        $exclusiveBuildplansByRump = [];
        $exclusiveModulesByRump = [];

        foreach ($rumps as $key => $rump) {
            $rumpId = $rump->getId();
            $hangar = $this->buildplanHangarRepository->getByRump($rumpId);

            if ($hangar !== null) {
                $modules = $this->getModulesByRumpCosts($rumpId);
                if ($modules === []) {
                    continue;
                }

                $validRumps[$key] = $rump;
                $exclusiveBuildplansByRump[$rumpId] = $hangar->getBuildplan();
                $exclusiveModulesByRump[$rumpId] = $modules;
                continue;
            }

            if ($rump->isStation()) {
                $buildplan = $this->spacecraftBuildplanRepository->getStationBuildplanByRump($rumpId);
                if ($buildplan === null) {
                    continue;
                }

                $exclusiveBuildplansByRump[$rumpId] = $buildplan;
                $exclusiveModulesByRump[$rumpId] = $this->getStationBuildplanModules($colony, $buildplan);
            }

            $validRumps[$key] = $rump;
        }

        return [$validRumps, $exclusiveBuildplansByRump, $exclusiveModulesByRump];
    }

    /**
     * @return array<int, array{module: Module, requiredAmount: int}>
     */
    private function getModulesByRumpCosts(int $rumpId): array
    {
        $commodityIds = [];
        foreach ($this->shipRumpCostRepository->getByShipRump($rumpId) as $cost) {
            $commodityIds[$cost->getCommodityId()] = $cost->getCommodityId();
        }

        if ($commodityIds === []) {
            return [];
        }

        $modulesByCommodityId = [];
        foreach ($this->moduleRepository->getByCommodityIds(array_values($commodityIds)) as $module) {
            $modulesByCommodityId[$module->getCommodityId()] = $module;
        }

        foreach ($commodityIds as $commodityId) {
            if (!array_key_exists($commodityId, $modulesByCommodityId)) {
                return [];
            }
        }

        return array_map(
            fn (Module $module): array => ['module' => $module, 'requiredAmount' => 1],
            array_values($modulesByCommodityId)
        );
    }

    /**
     * @return array<int, array{module: Module, requiredAmount: int}>
     */
    private function getStationBuildplanModules(Colony $colony, SpacecraftBuildplan $buildplan): array
    {
        $moduleRequirements = [];
        foreach ($buildplan->getModules() as $buildplanModule) {
            $module = $buildplanModule->getModule();
            $moduleRequirements[$module->getId()] = [
                'module' => $module,
                'requiredAmount' => $buildplanModule->getModuleCount()
            ];
        }

        foreach (
            $this->moduleRepository->getBySpecialTypeAndRump(
                $colony,
                SpacecraftModuleTypeEnum::SPECIAL,
                $buildplan->getRumpId()
            ) as $module
        ) {
            if (!array_key_exists($module->getId(), $moduleRequirements)) {
                $moduleRequirements[$module->getId()] = [
                    'module' => $module,
                    'requiredAmount' => 1
                ];
            }
        }

        return array_values($moduleRequirements);
    }

    /**
     * @param array<int, array{module: Module, requiredAmount: int}> $moduleRequirements
     * @param array<int, ModuleFabricationListItem> $allModules
     */
    private function addRumpToModules(SpacecraftRump $rump, array $moduleRequirements, array &$allModules): void
    {
        foreach ($moduleRequirements as $moduleRequirement) {
            $module = $moduleRequirement['module'];
            if (array_key_exists($module->getId(), $allModules)) {
                $allModules[$module->getId()]->addRump($rump, $moduleRequirement['requiredAmount']);
            }
        }
    }

    /**
     * @param array<int, array{module: Module, requiredAmount: int}> $moduleRequirements
     * @param array<int, ModuleFabricationListItem> $allModules
     */
    private function addBuildplanToModules(
        SpacecraftBuildplan $buildplan,
        array $moduleRequirements,
        array &$allModules
    ): void {
        foreach ($moduleRequirements as $moduleRequirement) {
            $module = $moduleRequirement['module'];
            if (array_key_exists($module->getId(), $allModules)) {
                $allModules[$module->getId()]->addBuildplan($buildplan, $moduleRequirement['requiredAmount']);
            }
        }
    }
}
