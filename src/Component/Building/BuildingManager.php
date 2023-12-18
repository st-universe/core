<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Manages actions relating to buildings on planets
 */
final class BuildingManager implements BuildingManagerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonySandboxRepositoryInterface $colonySandboxRepository;

    private BuildingPostActionInterface $buildingPostAction;

    private BuildingFunctionActionMapperInterface $buildingFunctionActionMapper;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonySandboxRepositoryInterface $colonySandboxRepository,
        BuildingFunctionActionMapperInterface $buildingFunctionActionMapper,
        BuildingPostActionInterface $buildingPostAction
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
        $this->colonySandboxRepository = $colonySandboxRepository;
        $this->buildingFunctionActionMapper = $buildingFunctionActionMapper;
        $this->buildingPostAction = $buildingPostAction;
    }

    public function activate(PlanetFieldInterface $field): bool
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

        $host = $field->getHost();

        $workerAmount = $building->getWorkers();

        if ($host instanceof ColonyInterface) {
            $worklessAmount = $host->getWorkless();
            if ($worklessAmount < $workerAmount) {
                return false;
            }

            $host->setWorkless($worklessAmount - $workerAmount);
        }

        $host
            ->setWorkers($host->getWorkers() + $workerAmount)
            ->setMaxBev($host->getMaxBev() + $building->getHousing());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleActivation($building, $host);

        $this->saveHost($host);

        return true;
    }

    public function deactivate(PlanetFieldInterface $field): void
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

        $host = $field->getHost();

        $this->updateWorkerAndMaxBev($building, $host);
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleDeactivation($building, $host);

        $this->saveHost($host);
    }

    private function saveHost(ColonyInterface|ColonySandboxInterface $host): void
    {
        if ($host instanceof ColonyInterface) {
            $this->colonyRepository->save($host);
        } else {
            $this->colonySandboxRepository->save($host);
        }
    }

    private function updateWorkerAndMaxBev(BuildingInterface $building, ColonyInterface|ColonySandboxInterface $host): void
    {
        $workerAmount = $building->getWorkers();

        if ($host instanceof ColonyInterface) {
            $host->setWorkless($host->getWorkless() + $workerAmount);
        }
        $host->setWorkers($host->getWorkers() - $workerAmount);
        $host->setMaxBev($host->getMaxBev() - $building->getHousing());
    }

    public function remove(PlanetFieldInterface $field, bool $isDueToUpgrade = false): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$isDueToUpgrade && !$building->isRemovable()) {
            return;
        }

        $host = $field->getHost();

        if (!$field->isUnderConstruction()) {
            $this->deactivate($field);
            $host
                ->setMaxStorage($host->getMaxStorage() - $building->getStorage())
                ->setMaxEps($host->getMaxEps() - $building->getEpsStorage());
        }

        foreach ($building->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null && $host instanceof ColonyInterface) {
                $handler->destruct($buildingFunctionId, $host);
            }
        }

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->saveHost($host);
    }

    public function finish(PlanetFieldInterface $field, bool $activate = true): ?string
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return null;
        }

        $host = $field->getHost();


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

        $host
            ->setMaxStorage($host->getMaxStorage() + $building->getStorage())
            ->setMaxEps($host->getMaxEps() + $building->getEpsStorage());

        $this->saveHost($host);
        $this->planetFieldRepository->save($field);

        return $activationDetails;
    }
}
