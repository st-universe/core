<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ColonyLibFactory implements ColonyLibFactoryInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private CommodityConsumptionInterface $commodityConsumption;

    private ShipRepositoryInterface $shipRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        CommodityConsumptionInterface $commodityConsumption,
        ShipRepositoryInterface $shipRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ResearchedRepositoryInterface $researchedRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->commodityConsumption = $commodityConsumption;
        $this->shipRepository = $shipRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->researchedRepository = $researchedRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtilFactory = $loggerUtilFactory;
    }

    public function createOrbitShipItem(
        ShipInterface $ship,
        int $ownerUserId
    ): OrbitShipItemInterface {
        return new OrbitShipItem(
            $this->torpedoTypeRepository,
            $ship,
            $ownerUserId
        );
    }

    public function createOrbitFleetItem(
        int $fleetId,
        array $shipList,
        int $ownerUserId
    ): OrbitFleetItemInterface {
        return new OrbitFleetItem(
            $fleetId,
            $shipList,
            $ownerUserId
        );
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
            $this->entityManager,
            $this->loggerUtilFactory->getLoggerUtil(),
            $colony,
            $buildingId,
            $showUnderground
        );
    }

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface {
        return new ColonyListItem(
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
}
