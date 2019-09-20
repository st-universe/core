<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use ColonyData;
use ShipData;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyLibFactory implements ColonyLibFactoryInterface
{
    private $planetFieldRepository;

    private $buildingRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
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
        ColonyData $colony,
        ?int $buildingId = null
    ): ColonySurfaceInterface {
        return new ColonySurface(
            $this->planetFieldRepository,
            $this->buildingRepository,
            $colony,
            $buildingId
        );
    }
}