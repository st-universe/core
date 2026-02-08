<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Module\Colony\Lib\Gui\ColonyScanPanel;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;

interface ColonyLibFactoryInterface
{
    /** @param array<BuildingFunction> $buildingfunctions */
    public function createBuildingFunctionWrapper(
        array $buildingfunctions
    ): BuildingFunctionWrapperInterface;

    public function createColonySurface(
        PlanetFieldHostInterface $host,
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        Colony $colony
    ): ColonyListItemInterface;

    public function createBuildableRumpItem(
        SpacecraftRump $shipRump,
        User $currentUser
    ): BuildableRumpListItemInterface;

    public function createColonyProductionPreviewWrapper(
        Building $building,
        PlanetFieldHostInterface $host
    ): ColonyProductionPreviewWrapper;

    public function createEpsProductionPreviewWrapper(
        PlanetFieldHostInterface $host,
        Building $building
    ): ColonyEpsProductionPreviewWrapper;

    public function createModuleSelector(
        SpacecraftModuleTypeEnum $type,
        Colony|Spacecraft $host,
        SpacecraftRump $rump,
        User $user,
        ?SpacecraftBuildplan $buildplan = null
    ): ModuleSelector;

    public function createColonyProduction(
        Commodity $commodity,
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
        ?array $production = null
    ): ColonyPopulationCalculatorInterface;

    public function createColonyScanPanel(
        Colony $colony
    ): ColonyScanPanel;
}
