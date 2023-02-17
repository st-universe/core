<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Shields\ColonyShieldingManager;
use Stu\Component\Colony\Shields\ColonyShieldingManagerInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
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

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private TalPageInterface $talPage;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    private CommodityRepositoryInterface $commodityRepository;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

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
        TalPageInterface $talPage,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        CommodityRepositoryInterface $commodityRepository,
        ColonyFunctionManagerInterface $colonyFunctionManager,
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
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->buildingCommodityRepository = $buildingCommodityRepository;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->talPage = $talPage;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
        $this->commodityRepository = $commodityRepository;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function createBuildingFunctionTal(
        array $buildingFunctionIds
    ): BuildingFunctionTalInterface {
        return new BuildingFunctionTal($buildingFunctionIds);
    }

    public function createColonySurface(
        ColonyInterface $colony,
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
            $this->loggerUtilFactory->getLoggerUtil(),
            $this->planetFieldTypeRetriever,
            $colony,
            $buildingId,
            $showUnderground
        );
    }

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface {
        return new ColonyListItem(
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
        array $production
    ): ColonyProductionPreviewWrapper {
        return new ColonyProductionPreviewWrapper(
            $this,
            $this->buildingCommodityRepository,
            $production
        );
    }

    public function createEpsProductionPreviewWrapper(
        ColonyInterface $colony
    ): ColonyEpsProductionPreviewWrapper {
        return new ColonyEpsProductionPreviewWrapper(
            $this->planetFieldRepository,
            $this->buildingRepository,
            $colony
        );
    }

    public function createModuleSelector(
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $ship,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ): ModuleSelector {
        return new ModuleSelector(
            $this->moduleRepository,
            $this->shipRumpModuleLevelRepository,
            $this->talPage,
            $moduleType,
            $colony,
            $ship,
            $rump,
            $userId,
            $buildplan
        );
    }

    public function createModuleSelectorSpecial(
        int $moduleType,
        ?ColonyInterface $colony,
        ?ShipInterface $ship,
        ShipRumpInterface $rump,
        int $userId,
        ?ShipBuildplanInterface $buildplan = null
    ): ModuleSelectorSpecial {
        return new ModuleSelectorSpecial(
            $this->moduleRepository,
            $this->shipRumpModuleLevelRepository,
            $this->talPage,
            $moduleType,
            $colony,
            $ship,
            $rump,
            $userId,
            $buildplan
        );
    }

    public function createColonyProduction(
        array &$production = []
    ): ColonyProduction {
        return new ColonyProduction(
            $this->commodityRepository,
            $production
        );
    }

    public function createColonyShieldingManager(
        ColonyInterface $colony
    ): ColonyShieldingManagerInterface {
        return new ColonyShieldingManager(
            $this->planetFieldRepository,
            $this->colonyFunctionManager,
            $colony
        );
    }
}
