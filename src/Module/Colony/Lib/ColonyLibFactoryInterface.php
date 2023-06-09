<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

interface ColonyLibFactoryInterface
{
    public function createBuildingFunctionTal(
        array $buildingFunctionIds
    ): BuildingFunctionTalInterface;

    public function createColonySurface(
        ColonyInterface $colony,
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface;

    public function createBuildableRumpItem(
        ShipRumpInterface $shipRump,
        UserInterface $currentUser
    ): BuildableRumpListItemInterface;

    /**
     * @param array<ColonyProduction> $production
     */
    public function createColonyProductionPreviewWrapper(
        array $production
    ): ColonyProductionPreviewWrapper;

    public function createEpsProductionPreviewWrapper(
        ColonyInterface $colony
    ): ColonyEpsProductionPreviewWrapper;

    public function createModuleSelector(
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $station,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ): ModuleSelector;

    public function createModuleSelectorSpecial(
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $station,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ): ModuleSelectorSpecial;

    /**
     * @param array{gc?: int, pc?: int, commodity_id?: int} $production
     */
    public function createColonyProduction(
        array &$production = []
    ): ColonyProduction;

    public function createColonyShieldingManager(
        ColonyInterface $colony
    ): ColonyShieldingManagerInterface;

    public function createColonyCommodityProduction(
        ColonyInterface $colony
    ): ColonyCommodityProductionInterface;

    public function createColonyProductionSumReducer(): ColonyProductionSumReducerInterface;

    /**
     * @param array<int, ColonyProduction> $production if null, use production of colony itself
     */
    public function createColonyPopulationCalculator(
        ColonyInterface $colony,
        array $production = null
    ): ColonyPopulationCalculatorInterface;
}
