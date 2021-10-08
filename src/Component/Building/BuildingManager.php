<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Colony\Lib\ModuleQueueLibInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingManager implements BuildingManagerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ModuleQueueLibInterface $moduleQueueLib;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository,
        ModuleQueueLibInterface $moduleQueueLib,
        EntityManagerInterface $entityManager
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyRepository = $colonyRepository;
        $this->moduleQueueLib = $moduleQueueLib;
        $this->entityManager = $entityManager;
    }

    public function activate(PlanetFieldInterface $field): void
    {
        if (!$field->isActivateable()) {
            return;
        }
        if ($field->isActive()) {
            return;
        }
        if ($field->hasHighDamage()) {
            return;
        }

        $building = $field->getBuilding();
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
        $this->colonyRepository->save($colony);

        $building->postActivation($colony);
    }

    public function deactivate(PlanetFieldInterface $field): void
    {
        if (!$field->isActivateAble()) {
            return;
        }
        if (!$field->isActive()) {
            return;
        }

        $building = $field->getBuilding();
        $colony = $field->getColony();
        $workerAmount = $building->getWorkers();

        $colony->setWorkless($colony->getWorkless() + $workerAmount);
        $colony->setWorkers($colony->getWorkers() - $workerAmount);

        $colony->setMaxBev($colony->getMaxBev() - $building->getHousing());
        $field->setActive(0);

        $this->planetFieldRepository->save($field);
        $this->colonyRepository->save($colony);

        $this->entityManager->flush();

        $building->postDeactivation($colony);
        $this->consequences($building, $colony);
    }

    public function remove(
        PlanetFieldInterface $field,
        bool $upgrade = false
    ): void {
        if (!$field->hasBuilding()) {
            return;
        }

        $building = $field->getBuilding();

        if (!$building->isRemoveAble() && $upgrade === false) {
            return;
        }

        $colony = $field->getColony();

        $this->deactivate($field);

        $colony
            ->setMaxStorage($colony->getMaxStorage() - $building->getStorage())
            ->setMaxEps($colony->getMaxEps() - $building->getEpsStorage());

        $field->clearBuilding();

        $this->planetFieldRepository->save($field);
        $this->colonyRepository->save($colony);



        $this->consequences($building, $colony);
    }

    private function consequences($building, $colony)
    {
        if ($building->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR)) {
            $colony->setShields(0);
        } else if ($building->getFunctions()->containsKey(BuildingEnum::BUILDING_FUNCTION_SHIELD_BATTERY)) {
            $colony->setShields(min($colony->getShields(), $colony->getMaxShields()));
        } else if (!empty(array_intersect($building->getFunctions()->getKeys(), BuildingEnum::BUILDING_FUNCTION_MODULEFABS))) {
            $this->moduleQueueLib->cancelModuleQueues($colony, $building);
        }
    }

    public function finish(PlanetFieldInterface $field, bool $activate = true): void
    {
        $building = $field->getBuilding();
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
