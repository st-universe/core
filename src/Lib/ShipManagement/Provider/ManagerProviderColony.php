<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

class ManagerProviderColony implements ManagerProviderInterface
{
    private ColonyInterface $colony;

    private CrewCreatorInterface $crewCreator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    public function __construct(
        ColonyInterface $colony,
        CrewCreatorInterface $crewCreator,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ColonyStorageManagerInterface $colonyStorageManager
    ) {
        $this->colony = $colony;
        $this->crewCreator = $crewCreator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->colonyStorageManager = $colonyStorageManager;
    }

    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function lowerEps(int $amount): ManagerProviderInterface
    {
        $this->colony->lowerEps($amount);

        return $this;
    }

    public function getName(): string
    {
        return $this->colony->getName();
    }

    public function getSectorString(): string
    {
        return $this->colony->getSectorString();
    }

    public function getFreeCrewAmount(): int
    {
        return $this->colony->getCrewAssignmentAmount();
    }

    public function createShipCrew(ShipInterface $ship): void
    {
        $this->crewCreator->createShipCrew($ship, $this->colony);
    }

    public function isAbleToStoreCrew(int $amount): bool
    {
        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $this->colony
        )->getFreeAssignmentCount();

        return $freeAssignmentCount >= $amount;
    }

    public function addCrewAssignments(Collection $crewAssignments): void
    {
        foreach ($crewAssignments as $crewAssignment) {
            $this->colony->getCrewAssignments()->add($crewAssignment);

            $crewAssignment->setColony($this->colony);
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $this->shipCrewRepository->save($crewAssignment);
        }
    }

    public function getStorage(): Collection
    {
        return $this->colony->getStorage();
    }

    public function upperStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->colonyStorageManager->upperStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }

    public function lowerStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->colonyStorageManager->lowerStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }
}
