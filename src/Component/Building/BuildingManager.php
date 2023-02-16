<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

/**
 * Manages actions relating to buildings on planets
 */
final class BuildingManager implements BuildingManagerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private BuildingPostActionInterface $buildingPostAction;

    private BuildingFunctionActionMapperInterface $buildingFunctionActionMapper;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository,
        BuildingFunctionActionMapperInterface $buildingFunctionActionMapper,
        BuildingPostActionInterface $buildingPostAction
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
        $this->buildingFunctionActionMapper = $buildingFunctionActionMapper;
        $this->buildingPostAction = $buildingPostAction;
    }

    public function activate(PlanetFieldInterface $field): void
    {
        $building = $field->getBuilding();

        if ($building === null) {
            return;
        }

        if (!$field->isActivateable()) {
            return;
        }

        if ($field->isActive()) {
            return;
        }

        if ($field->hasHighDamage()) {
            return;
        }

        $colony = $field->getColony();

        $workerAmount = $building->getWorkers();
        $worklessAmount = $colony->getWorkless();

        if ($worklessAmount < $workerAmount) {
            return;
        }

        $colony
            ->setWorkless($worklessAmount - $workerAmount)
            ->setWorkers($colony->getWorkers() + $workerAmount)
            ->setMaxBev($colony->getMaxBev() + $building->getHousing());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleActivation($building, $colony);

        $this->colonyRepository->save($colony);
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

        $colony = $field->getColony();

        $this->updateWorkerAndMaxBev($building, $colony);
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $this->buildingPostAction->handleDeactivation($building, $colony);

        $this->colonyRepository->save($colony);
    }

    private function updateWorkerAndMaxBev(BuildingInterface $building, ColonyInterface $colony): void
    {
        $workerAmount = $building->getWorkers();
        $colony->setWorkless($colony->getWorkless() + $workerAmount);
        $colony->setWorkers($colony->getWorkers() - $workerAmount);

        $colony->setMaxBev($colony->getMaxBev() - $building->getHousing());
    }

    public function remove(
        PlanetFieldInterface $field,
        bool $upgrade = false
    ): void {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        if (!$building->isRemovable() && $upgrade === false) {
            return;
        }

        $colony = $field->getColony();
        $colonyId = $colony->getId();

        if (!$field->isUnderConstruction()) {
            $this->deactivate($field);
            $colony
                ->setMaxStorage($colony->getMaxStorage() - $building->getStorage())
                ->setMaxEps($colony->getMaxEps() - $building->getEpsStorage());
        }

        foreach ($building->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null) {
                $handler->destruct($colonyId, $buildingFunctionId);
            }
        }

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->colonyRepository->save($colony);
    }

    public function finish(PlanetFieldInterface $field, bool $activate = true): void
    {
        $building = $field->getBuilding();
        if ($building === null) {
            return;
        }

        $colony = $field->getColony();

        $field
            ->setActive(0)
            ->setIntegrity($building->getIntegrity());

        if ($building->isActivateAble() && $activate === true) {
            $this->activate($field);
        }

        $colony
            ->setMaxStorage($colony->getMaxStorage() + $building->getStorage())
            ->setMaxEps($colony->getMaxEps() + $building->getEpsStorage());

        $this->colonyRepository->save($colony);
        $this->planetFieldRepository->save($field);
    }
}
