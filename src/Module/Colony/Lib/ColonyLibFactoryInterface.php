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
use Stu\Orm\Entity\BuildingFunctionInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

interface ColonyLibFactoryInterface
{
    /** @param array<BuildingFunctionInterface> $buildingfunctions */
    public function createBuildingFunctionWrapper(
        array $buildingfunctions
    ): BuildingFunctionWrapperInterface;

    public function createColonySurface(
        PlanetFieldHostInterface $host,
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface;

    public function createBuildableRumpItem(
        SpacecraftRumpInterface $shipRump,
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
        SpacecraftModuleTypeEnum $type,
        ColonyInterface|SpacecraftInterface $host,
        SpacecraftRumpInterface $rump,
        UserInterface $user,
        ?SpacecraftBuildplanInterface $buildplan = null
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
        ?array $production = null
    ): ColonyPopulationCalculatorInterface;

    public function createColonyScanPanel(
        ColonyInterface $colony
    ): ColonyScanPanel;
}
