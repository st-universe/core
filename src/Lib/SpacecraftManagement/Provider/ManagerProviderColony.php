<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class ManagerProviderColony implements ManagerProviderInterface
{
    public function __construct(private ColonyInterface $colony, private CrewCreatorInterface $crewCreator, private ColonyLibFactoryInterface $colonyLibFactory, private StorageManagerInterface $storageManager, private TroopTransferUtilityInterface $troopTransferUtility) {}

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->colony->getUser();
    }

    #[Override]
    public function getEps(): int
    {
        return $this->colony->getChangeable()->getEps();
    }

    #[Override]
    public function lowerEps(int $amount): ManagerProviderInterface
    {
        $this->colony->getChangeable()->lowerEps($amount);

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
    public function addCrewAssignment(SpacecraftInterface $spacecraft, int $amount): void
    {
        $this->crewCreator->createCrewAssignment($spacecraft, $this->colony, $amount);
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
        $this->storageManager->upperStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }

    #[Override]
    public function lowerStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->storageManager->lowerStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }
}
