<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingFinishHandler
{
    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly ColonyBuildingEffects $colonyBuildingEffects,
        private readonly BuildingReactivationHandler $buildingReactivationHandler,
        private readonly BuildingActivationHandler $buildingActivationHandler
    ) {}

    public function finish(PlanetField $field, bool $activate = true): ?string
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return null;
        }

        $host = $field->getHost();
        $shouldReactivateOthers = $this->initializeFinishedField($field, $building);
        $shouldApplyUndergroundLogisticsActivation = $this->shouldApplyUndergroundLogisticsActivationAfterFinish(
            $host,
            $building
        );

        [$activationDetails, $wasActivated] = $this->createFinishActivationResult($field, $building, $activate);

        $this->updateStorageAndEpsAfterFinish($field, $building);
        if ($shouldApplyUndergroundLogisticsActivation && $wasActivated && $host instanceof Colony) {
            $this->handleUndergroundLogisticsActivation($building, $host);
        }

        $this->saveHost($field->getHost());
        $this->planetFieldRepository->save($field);

        $reactivatedCount = $shouldReactivateOthers
            ? $this->buildingReactivationHandler->handleAfterUpgradeFinish(
                $field,
                $wasActivated,
                fn(PlanetField $reactivationField): bool => $this->buildingActivationHandler->activate($reactivationField)
            )
            : 0;

        return $this->buildingReactivationHandler->appendReactivationDetails($activationDetails, $reactivatedCount);
    }

    private function saveHost(Colony|ColonySandbox $host): void
    {
        if ($host instanceof Colony) {
            $this->colonyRepository->save($host);
        } else {
            $this->colonySandboxRepository->save($host);
        }
    }

    private function initializeFinishedField(PlanetField $field, Building $building): bool
    {
        $field
            ->setActive(0)
            ->setIntegrity($building->getIntegrity());

        return $field->getReactivateAfterUpgrade() === $field->getId();
    }

    /**
     * @return array{0: ?string, 1: bool}
     */
    private function createFinishActivationResult(
        PlanetField $field,
        Building $building,
        bool $activate
    ): array {
        if (!$building->isActivateAble()) {
            return [null, false];
        }

        if (!$activate) {
            return ['Und wurde wunschgemäß nicht aktiviert', false];
        }

        $wasActivated = $this->buildingActivationHandler->activate($field);

        return [
            $wasActivated
                ? '[color=green]Und konnte wunschgemäß aktiviert werden[/color]'
                : '[color=red]Konnte allerdings nicht wie gewünscht aktiviert werden[/color]',
            $wasActivated
        ];
    }

    private function shouldApplyUndergroundLogisticsActivationAfterFinish(
        Colony|ColonySandbox $host,
        Building $building
    ): bool {
        if (!$host instanceof Colony) {
            return false;
        }

        if (!$this->colonyBuildingEffects->buildingProducesUndergroundLogistics($building)) {
            return false;
        }

        return !$this->colonyBuildingEffects->hasUndergroundLogisticsProduction($host);
    }

    private function updateStorageAndEpsAfterFinish(PlanetField $field, Building $building): void
    {
        $host = $field->getHost();
        if (!$this->shouldUpdateStorageAndEpsAfterFinish($host, $building)) {
            return;
        }

        $this->adjustStorageAndEps(
            $this->getChangeable($field),
            $building->getStorage(),
            $building->getEpsStorage()
        );
    }

    private function shouldUpdateStorageAndEpsAfterFinish(Colony|ColonySandbox $host, Building $building): bool
    {
        if (!$this->colonyBuildingEffects->buildingRequiresUndergroundLogistics($building)) {
            return true;
        }

        return $this->colonyBuildingEffects->hasEnoughUndergroundLogistics($host, $building);
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

    private function handleUndergroundLogisticsActivation(Building $building, Colony $host): void
    {
        $this->colonyBuildingEffects->adjustUndergroundLogisticsCapacity($building, $host, 1);
    }
}
