<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleFab;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

final class ShowModuleFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULEFAB';

    private ColonyLoaderInterface $colonyLoader;
    private ShowModuleFabRequestInterface $showModuleFabRequest;
    private ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository;
    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;
    private ModuleQueueRepositoryInterface $moduleQueueRepository;
    private ShipRumpRepositoryInterface $shipRumpRepository;
    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleFabRequestInterface $showModuleFabRequest,
        ModuleBuildingFunctionRepositoryInterface $moduleBuildingFunctionRepository,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleFabRequest = $showModuleFabRequest;
        $this->moduleBuildingFunctionRepository = $moduleBuildingFunctionRepository;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
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

        $sortedModules = [];
        foreach ($modules as $module) {
            $moduleType = $module->getModule()->getType()->value;
            $moduleLevel = $module->getModule()->getLevel();
            if (!isset($sortedModules[$moduleType])) {
                $sortedModules[$moduleType] = [];
            }
            if (!isset($sortedModules[$moduleType][$moduleLevel])) {
                $sortedModules[$moduleType][$moduleLevel] = [];
            }
            $sortedModules[$moduleType][$moduleLevel][] = new ModuleFabricationListItemTal(
                $this->moduleQueueRepository,
                $module->getModule(),
                $colony
            );
        }

        $shipRumps = $this->shipRumpRepository->getBuildableByUser($userId);

        $moduleTypes = [];
        foreach (ShipModuleTypeEnum::cases() as $moduleType) {
            $moduleTypes[$moduleType->value] = [
                'name' => $moduleType->getDescription(),
                'image' => "/assets/buttons/modul_screen_{$moduleType->value}.png"
            ];
        }
        $rumpModules = [];
        $rumpModules[0] = $sortedModules;
        foreach ($shipRumps as $rump) {
            $rumpId = $rump->getId();
            $rumpModules[$rumpId] = [];

            foreach ($sortedModules as $type => $levels) {
                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump($rumpId);

                if ($type === ShipModuleTypeEnum::SPECIAL->value) {
                    foreach ($levels as $level => $modules) {
                        if (!isset($rumpModules[$rumpId][$type])) {
                            $rumpModules[$rumpId][$type] = [];
                        }
                        $rumpModules[$rumpId][$type][$level] = $modules;
                    }
                } else {
                    $min_level_method = 'getModuleLevel' . $type . 'Min';
                    $max_level_method = 'getModuleLevel' . $type . 'Max';

                    if ($mod_level !== null && method_exists($mod_level, $min_level_method) && method_exists($mod_level, $max_level_method)) {
                        $min_level = $mod_level->$min_level_method();
                        $max_level = $mod_level->$max_level_method();

                        foreach ($levels as $level => $modules) {
                            if ($level >= $min_level && $level <= $max_level) {
                                if (!isset($rumpModules[$rumpId][$type])) {
                                    $rumpModules[$rumpId][$type] = [];
                                }
                                $rumpModules[$rumpId][$type][$level] = $modules;
                            }
                        }
                    }
                }
            }
        }

        $game->showMacro(ColonyMenuEnum::MENU_MODULEFAB->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_MODULEFAB);

        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('FUNC', $func);
        $game->setTemplateVar('SORTED_MODULES', $sortedModules);
        $game->setTemplateVar('SHIP_RUMPS', $shipRumps);
        $game->setTemplateVar('MODULE_TYPES', $moduleTypes);
        $game->setTemplateVar('RUMP_MODULES', $rumpModules);
    }
}
