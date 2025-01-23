<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyPopulationCalculator;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProduction;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducer;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManager;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Module\Colony\Lib\Gui\ColonyScanPanel;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

final class ColonyLibFactory implements ColonyLibFactoryInterface
{
    public function __construct(
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private BuildingRepositoryInterface $buildingRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private CommodityConsumptionInterface $commodityConsumption,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ResearchedRepositoryInterface $researchedRepository,
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private PlanetGeneratorInterface $planetGenerator,
        private EntityManagerInterface $entityManager,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private TwigPageInterface $twigPage,
        private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private ModuleSelectorAddonFactoryInterface $moduleSelectorAddonFactory,
        private CommodityCacheInterface $commodityCache,
        private StatusBarFactoryInterface $statusBarFactory,
        private PanelLayerCreationInterface $panelLayerCreation,
        private LoggerUtilFactoryInterface $loggerUtilFactory
    ) {}

    #[Override]
    public function createBuildingFunctionWrapper(
        array $buildingfunctions
    ): BuildingFunctionWrapperInterface {
        return new BuildingFunctionWrapper($buildingfunctions);
    }

    #[Override]
    public function createColonySurface(
        PlanetFieldHostInterface $host,
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface {
        return new ColonySurface(
            $this->planetFieldRepository,
            $this->buildingRepository,
            $this->colonyRepository,
            $this->researchedRepository,
            $this->planetGenerator,
            $this->entityManager,
            $this->planetFieldTypeRetriever,
            $host,
            $buildingId,
            $showUnderground
        );
    }

    #[Override]
    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface {
        return new ColonyListItem(
            $this,
            $this->planetFieldRepository,
            $this->commodityConsumption,
            $this->statusBarFactory,
            $colony,
            $this->flightSignatureRepository->getVisibleSignatureCount($colony)
        );
    }

    #[Override]
    public function createBuildableRumpItem(
        SpacecraftRumpInterface $shipRump,
        UserInterface $currentUser
    ): BuildableRumpListItemInterface {
        return new BuildableRumpListItem(
            $this->spacecraftRepository,
            $this->spacecraftBuildplanRepository,
            $shipRump,
            $currentUser
        );
    }

    #[Override]
    public function createColonyProductionPreviewWrapper(
        BuildingInterface $building,
        PlanetFieldHostInterface $host
    ): ColonyProductionPreviewWrapper {
        return new ColonyProductionPreviewWrapper(
            $this,
            $building,
            $this->createColonyCommodityProduction($host)->getProduction()
        );
    }

    #[Override]
    public function createEpsProductionPreviewWrapper(
        PlanetFieldHostInterface $host,
        BuildingInterface $building
    ): ColonyEpsProductionPreviewWrapper {
        return new ColonyEpsProductionPreviewWrapper(
            $this->planetFieldRepository,
            $host,
            $building
        );
    }

    #[Override]
    public function createModuleSelector(
        SpacecraftModuleTypeEnum $moduleType,
        ColonyInterface|SpacecraftInterface $host,
        SpacecraftRumpInterface $rump,
        UserInterface $user,
        ?SpacecraftBuildplanInterface $buildplan = null
    ): ModuleSelector {

        $addon = $this->moduleSelectorAddonFactory->createModuleSelectorAddon($moduleType);

        return new ModuleSelector(
            $this->moduleRepository,
            $this->shipRumpModuleLevelRepository,
            $this->twigPage,
            $moduleType,
            $host,
            $rump,
            $user,
            $addon,
            $buildplan
        );
    }

    #[Override]
    public function createColonyProduction(
        CommodityInterface $commodity,
        int $production,
        ?int $pc = null
    ): ColonyProduction {
        return new ColonyProduction(
            $commodity,
            $production,
            $pc
        );
    }

    #[Override]
    public function createColonyShieldingManager(
        PlanetFieldHostInterface $host
    ): ColonyShieldingManagerInterface {
        return new ColonyShieldingManager(
            $this->planetFieldRepository,
            $this->colonyFunctionManager,
            $host
        );
    }

    #[Override]
    public function createColonyCommodityProduction(
        PlanetFieldHostInterface $host
    ): ColonyCommodityProductionInterface {
        return new ColonyCommodityProduction(
            $this->buildingCommodityRepository,
            $host,
            $this,
            $this->commodityCache
        );
    }

    #[Override]
    public function createColonyProductionSumReducer(): ColonyProductionSumReducerInterface
    {
        return new ColonyProductionSumReducer();
    }

    #[Override]
    public function createColonyPopulationCalculator(
        PlanetFieldHostInterface $host,
        ?array $production = null
    ): ColonyPopulationCalculatorInterface {

        return new ColonyPopulationCalculator(
            $host,
            $production ?? $this->createColonyCommodityProduction($host)->getProduction()
        );
    }

    #[Override]
    public function createColonyScanPanel(
        ColonyInterface $colony
    ): ColonyScanPanel {
        return new ColonyScanPanel(
            $this->panelLayerCreation,
            $colony,
            $this->colonyFunctionManager,
            $this->loggerUtilFactory->getLoggerUtil()
        );
    }
}
