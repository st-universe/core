<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyPopulationCalculator;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProduction;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducer;
use Stu\Component\Colony\Commodity\ColonyProductionSumReducerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManager;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonFactoryInterface;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

final class ColonyLibFactory implements ColonyLibFactoryInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private CommodityConsumptionInterface $commodityConsumption;

    private ShipRepositoryInterface $shipRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private PlanetGeneratorInterface $planetGenerator;

    private EntityManagerInterface $entityManager;

    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TwigPageInterface $twigPage;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private ModuleSelectorAddonFactoryInterface $moduleSelectorAddonFactory;

    private CommodityCacheInterface $commodityCache;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        CommodityConsumptionInterface $commodityConsumption,
        ShipRepositoryInterface $shipRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ResearchedRepositoryInterface $researchedRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        PlanetGeneratorInterface $planetGenerator,
        EntityManagerInterface $entityManager,
        BuildingCommodityRepositoryInterface $buildingCommodityRepository,
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        TwigPageInterface $twigPage,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ModuleSelectorAddonFactoryInterface $moduleSelectorAddonFactory,
        CommodityCacheInterface $commodityCache,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->commodityConsumption = $commodityConsumption;
        $this->shipRepository = $shipRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->researchedRepository = $researchedRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->planetGenerator = $planetGenerator;
        $this->entityManager = $entityManager;
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->twigPage = $twigPage;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->moduleSelectorAddonFactory = $moduleSelectorAddonFactory;
        $this->commodityCache = $commodityCache;
        $this->loggerUtilFactory = $loggerUtilFactory;
    }

    public function createBuildingFunctionTal(
        array $buildingFunctionIds
    ): BuildingFunctionTalInterface {
        return new BuildingFunctionTal($buildingFunctionIds);
    }

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
            $showUnderground,
            $this->loggerUtilFactory->getLoggerUtil()
        );
    }

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface {
        return new ColonyListItem(
            $this,
            $this->planetFieldRepository,
            $this->commodityConsumption,
            $colony,
            $this->flightSignatureRepository->getVisibleSignatureCount($colony)
        );
    }

    public function createBuildableRumpItem(
        ShipRumpInterface $shipRump,
        UserInterface $currentUser
    ): BuildableRumpListItemInterface {
        return new BuildableRumpListItem(
            $this->shipRepository,
            $this->shipBuildplanRepository,
            $shipRump,
            $currentUser
        );
    }

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

    public function createModuleSelector(
        ShipModuleTypeEnum $moduleType,
        ColonyInterface|ShipInterface $host,
        ShipRumpInterface $rump,
        UserInterface $user,
        ?ShipBuildplanInterface $buildplan = null
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

    public function createColonyShieldingManager(
        PlanetFieldHostInterface $host
    ): ColonyShieldingManagerInterface {
        return new ColonyShieldingManager(
            $this->planetFieldRepository,
            $this->colonyFunctionManager,
            $host
        );
    }

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

    public function createColonyProductionSumReducer(): ColonyProductionSumReducerInterface
    {
        return new ColonyProductionSumReducer();
    }

    public function createColonyPopulationCalculator(
        PlanetFieldHostInterface $host,
        array $production = null
    ): ColonyPopulationCalculatorInterface {

        return new ColonyPopulationCalculator(
            $host,
            $production ?? $this->createColonyCommodityProduction($host)->getProduction()
        );
    }
}
