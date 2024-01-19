<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

class ManagerProviderColony implements ManagerProviderInterface
{
    private ColonyInterface $colony;

    private CrewCreatorInterface $crewCreator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private TroopTransferUtilityInterface $troopTransferUtility;

    public function __construct(
        ColonyInterface $colony,
        CrewCreatorInterface $crewCreator,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyStorageManagerInterface $colonyStorageManager,
        TroopTransferUtilityInterface $troopTransferUtility
    ) {
        $this->colony = $colony;
        $this->crewCreator = $crewCreator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->troopTransferUtility = $troopTransferUtility;
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
        return sprintf('Kolonie %s', $this->colony->getName());
    }

    public function getSectorString(): string
    {
        return $this->colony->getSectorString();
    }

    public function getFreeCrewAmount(): int
    {
        return $this->colony->getCrewAssignmentAmount();
    }

    public function addShipCrew(ShipInterface $ship, int $amount): void
    {
        $this->crewCreator->createShipCrew($ship, $this->colony, $amount);
    }

    public function getFreeCrewStorage(): int
    {
        return $this->colonyLibFactory->createColonyPopulationCalculator(
            $this->colony
        )->getFreeAssignmentCount();
    }

    public function addCrewAssignments(array $crewAssignments): void
    {
        foreach ($crewAssignments as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $this->colony);
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
