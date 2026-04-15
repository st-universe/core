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

final class BuildingActivationHandler
{
    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly BuildingPostActionInterface $buildingPostAction
    ) {}

    public function activate(PlanetField $field): bool
    {
        $building = $field->getBuilding();

        if ($building === null) {
            return false;
        }

        if (!$field->isActivateable()) {
            return false;
        }

        if ($field->isActive()) {
            return true;
        }

        if ($field->hasHighDamage()) {
            return false;
        }

        $changeable = $this->getChangeable($field);

        $workerAmount = $building->getWorkers();

        if ($changeable instanceof ColonyChangeable) {
            $worklessAmount = $changeable->getWorkless();
            if ($worklessAmount < $workerAmount) {
                return false;
            }

            $changeable->setWorkless($worklessAmount - $workerAmount);
        }

        $changeable
            ->setWorkers($changeable->getWorkers() + $workerAmount)
            ->setMaxBev($changeable->getMaxBev() + $building->getHousing());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleActivation($building, $field->getHost());

        $this->saveHost($field->getHost());

        return true;
    }

    public function deactivate(PlanetField $field): void
    {
        $building = $field->getBuilding();

        if ($building === null) {
            return;
        }

        if (!$field->isActivateable()) {
            return;
        }

        if (!$field->isActive()) {
            return;
        }

        $changeable = $this->getChangeable($field);

        $this->updateWorkerAndMaxBev($building, $changeable);
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleDeactivation($building, $field->getHost());

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

    private function updateWorkerAndMaxBev(Building $building, ColonyChangeable|ColonySandbox $host): void
    {
        $workerAmount = $building->getWorkers();

        if ($host instanceof ColonyChangeable) {
            $host->setWorkless($host->getWorkless() + $workerAmount);
        }
        $host->setWorkers($host->getWorkers() - $workerAmount);
        $host->setMaxBev($host->getMaxBev() - $building->getHousing());
    }

    private function getChangeable(PlanetField $field): ColonyChangeable|ColonySandbox
    {
        $host = $field->getHost();

        return $host instanceof ColonySandbox
            ? $host
            : $host->getChangeable();
    }
}

