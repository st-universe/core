<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Override;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Manages actions relating to buildings on planets
 */
final class BuildingManager implements BuildingManagerInterface
{
    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly BuildingFunctionActionMapperInterface $buildingFunctionActionMapper,
        private readonly BuildingPostActionInterface $buildingPostAction
    ) {}

    #[Override]
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

    #[Override]
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

    #[Override]
    public function remove(PlanetField $field, bool $isDueToUpgrade = false): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$isDueToUpgrade && !$building->isRemovable()) {
            return;
        }

        $host = $field->getHost();
        $changeable = $this->getChangeable($field);

        if (!$field->isUnderConstruction()) {
            $this->deactivate($field);
            $changeable
                ->setMaxStorage($changeable->getMaxStorage() - $building->getStorage())
                ->setMaxEps($changeable->getMaxEps() - $building->getEpsStorage());
        }

        foreach ($building->getFunctions() as $function) {
            $buildingFunction = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunction);
            if ($handler !== null && $host instanceof Colony) {
                $handler->destruct($buildingFunction, $host);
            }
        }

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->saveHost($field->getHost());
    }

    #[Override]
    public function finish(PlanetField $field, bool $activate = true): ?string
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return null;
        }

        $changeable = $this->getChangeable($field);

        $field
            ->setActive(0)
            ->setIntegrity($building->getIntegrity());

        $activationDetails = null;
        if ($building->isActivateAble()) {
            if ($activate) {
                $activationDetails = $this->activate($field)
                    ? '[color=green]Und konnte wunschgemäß aktiviert werden[/color]'
                    : '[color=red]Konnte allerdings nicht wie gewünscht aktiviert werden[/color]';
            } else {
                $activationDetails = 'Und wurde wunschgemäß nicht aktiviert';
            }
        }

        $changeable
            ->setMaxStorage($changeable->getMaxStorage() + $building->getStorage())
            ->setMaxEps($changeable->getMaxEps() + $building->getEpsStorage());

        $this->saveHost($field->getHost());
        $this->planetFieldRepository->save($field);

        return $activationDetails;
    }

    private function getChangeable(PlanetField $field): ColonyChangeable|ColonySandbox
    {
        $host = $field->getHost();

        return $host instanceof ColonySandbox
            ? $host
            : $host->getChangeable();
    }
}
