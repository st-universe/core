<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Override;
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
    public function __construct(private ColonyInterface $colony, private CrewCreatorInterface $crewCreator, private ColonyLibFactoryInterface $colonyLibFactory, private ColonyStorageManagerInterface $colonyStorageManager, private TroopTransferUtilityInterface $troopTransferUtility)
    {
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

    #[Override]
    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    #[Override]
    public function lowerEps(int $amount): ManagerProviderInterface
    {
        $this->colony->lowerEps($amount);

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return sprintf('Kolonie %s', $this->colony->getName());
    }

    #[Override]
    public function getSectorString(): string
    {
        return $this->colony->getSectorString();
    }

    #[Override]
    public function getFreeCrewAmount(): int
    {
        return $this->colony->getCrewAssignmentAmount();
    }

    #[Override]
    public function addShipCrew(ShipInterface $ship, int $amount): void
    {
        $this->crewCreator->createShipCrew($ship, $this->colony, $amount);
    }

    #[Override]
    public function getFreeCrewStorage(): int
    {
        return $this->colonyLibFactory->createColonyPopulationCalculator(
            $this->colony
        )->getFreeAssignmentCount();
    }

    #[Override]
    public function addCrewAssignments(array $crewAssignments): void
    {
        foreach ($crewAssignments as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $this->colony);
        }
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->colony->getStorage();
    }

    #[Override]
    public function upperStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->colonyStorageManager->upperStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }

    #[Override]
    public function lowerStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->colonyStorageManager->lowerStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }
}
