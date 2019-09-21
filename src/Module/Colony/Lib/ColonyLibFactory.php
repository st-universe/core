<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ShipData;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyLibFactory implements ColonyLibFactoryInterface
{
    private $planetFieldRepository;

    private $buildingRepository;

    private $commodityRepository;

    private $colonyRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        CommodityRepositoryInterface $commodityRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->commodityRepository = $commodityRepository;
        $this->colonyRepository = $colonyRepository;
    }

    public function createOrbitShipItem(
        ShipData $ship,
        int $ownerUserId
    ): OrbitShipItemInterface {
        return new OrbitShipItem(
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
        ?int $buildingId = null
    ): ColonySurfaceInterface {
        return new ColonySurface(
            $this->planetFieldRepository,
            $this->buildingRepository,
            $this->colonyRepository,
            $colony,
            $buildingId
        );
    }

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface {
        return new ColonyListItem(
            $this->commodityRepository,
            $colony
        );
    }
}