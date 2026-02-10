<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingRemovalHandler
{
    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly BuildingFunctionActionMapperInterface $buildingFunctionActionMapper,
        private readonly ColonyBuildingEffects $colonyBuildingEffects,
        private readonly BuildingActivationHandler $buildingActivationHandler
    ) {}

    public function remove(PlanetField $field, bool $isDueToUpgrade = false): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$this->canRemoveBuilding($building, $isDueToUpgrade)) {
            return;
        }

        $host = $field->getHost();
        if (!$field->isUnderConstruction()) {
            $this->handleRemovalWhenNotUnderConstruction($field, $building, $host, $isDueToUpgrade);
        }

        $this->destructBuildingFunctions($building, $host);

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->saveHost($field->getHost());
    }

    private function saveHost(Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyRepository->save($host);
        } else {
            $this->colonySandboxRepository->save($host);
        }
    }

    private function canRemoveBuilding(Building $building, bool $isDueToUpgrade): bool
    {
        return $isDueToUpgrade || $building->isRemovable();
    }

    private function handleRemovalWhenNotUnderConstruction(
        PlanetField $field,
        Building $building,
        Colony|ColonySandbox $host,
        bool $isDueToUpgrade
    ): void {
        if ($field->isActive() && $host instanceof Colony) {
            $this->handleUndergroundLogisticsRemoval($building, $host);
            if (!$isDueToUpgrade) {
                $this->handleOrbitalMaintenanceRemoval($building, $host);
            }
        }

        $this->buildingActivationHandler->deactivate($field);
        $this->updateStorageAndEpsAfterRemoval($field, $building, $host);
    }

    private function updateStorageAndEpsAfterRemoval(
        PlanetField $field,
        Building $building,
        Colony|ColonySandbox $host
    ): void {
        if (!$this->shouldUpdateStorageAndEpsAfterRemoval($host, $building)) {
            return;
        }

        $this->adjustStorageAndEps(
            $this->getChangeable($field),
            -$building->getStorage(),
            -$building->getEpsStorage()
        );
    }

    private function shouldUpdateStorageAndEpsAfterRemoval(Colony|ColonySandbox $host, Building $building): bool
    {
        if (!$this->colonyBuildingEffects->buildingRequiresUndergroundLogistics($building)) {
            return true;
        }

        return $this->colonyBuildingEffects->hasUndergroundLogisticsProduction($host);
    }

    private function destructBuildingFunctions(Building $building, Colony|ColonySandbox $host): void
    {
        foreach ($building->getFunctions() as $function) {
            $buildingFunction = $function->getFunction();
            $handler = $this->buildingFunctionActionMapper->map($buildingFunction);

            if ($handler !== null && $host instanceof Colony) {
                $handler->destruct($buildingFunction, $host);
            }
        }
    }

    private function adjustStorageAndEps(ColonyChangeable|ColonySandbox $changeable, int $storageDelta, int $epsDelta): void
    {
        if ($storageDelta === 0 && $epsDelta === 0) {
            return;
        }

        $changeable
            ->setMaxStorage($changeable->getMaxStorage() + $storageDelta)
            ->setMaxEps($changeable->getMaxEps() + $epsDelta);
    }

    private function getChangeable(PlanetField $field): ColonyChangeable|ColonySandbox
    {
        $host = $field->getHost();

        return $host instanceof ColonySandbox
            ? $host
            : $host->getChangeable();
    }

    private function handleUndergroundLogisticsRemoval(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, -1);
    }

    private function handleOrbitalMaintenanceRemoval(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->deactivateOrbitalMaintenanceConsumers(
            $building,
            $host,
            function (PlanetField $field): void {
                $this->buildingActivationHandler->deactivate($field);
            }
        );
    }
}
