<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
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
        PlanetFieldHostInterface $host,
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

    public function createColonyProductionPreviewWrapper(
        BuildingInterface $building,
        PlanetFieldHostInterface $host
    ): ColonyProductionPreviewWrapper;

    public function createEpsProductionPreviewWrapper(
        PlanetFieldHostInterface $host,
        BuildingInterface $building
    ): ColonyEpsProductionPreviewWrapper;

    public function createModuleSelector(
        ShipModuleTypeEnum $type,
        ColonyInterface|ShipInterface $host,
        ShipRumpInterface $rump,
        UserInterface $user,
        ?ShipBuildplanInterface $buildplan = null
    ): ModuleSelector;

    public function createColonyProduction(
        CommodityInterface $commodity,
        int $production,
        ?int $pc = null
    ): ColonyProduction;


    public function createColonyShieldingManager(
        PlanetFieldHostInterface $host
    ): ColonyShieldingManagerInterface;

    public function createColonyCommodityProduction(
        PlanetFieldHostInterface $host
    ): ColonyCommodityProductionInterface;

    public function createColonyProductionSumReducer(): ColonyProductionSumReducerInterface;

    /**
     * @param ColonyProduction[]|null $production if null, use production of colony itself
     */
    public function createColonyPopulationCalculator(
        PlanetFieldHostInterface $host,
        array $production = null
    ): ColonyPopulationCalculatorInterface;
}
