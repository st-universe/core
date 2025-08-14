<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

class ManagerProviderColony implements ManagerProviderInterface
{
    public function __construct(private Colony $colony, private CrewCreatorInterface $crewCreator, private ColonyLibFactoryInterface $colonyLibFactory, private StorageManagerInterface $storageManager, private TroopTransferUtilityInterface $troopTransferUtility) {}

    #[Override]
    public function getUser(): User
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
    public function addCrewAssignment(Spacecraft $spacecraft, int $amount): void
    {
        $this->crewCreator->createCrewAssignments($spacecraft, $this->colony, $amount);
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
    public function upperStorage(Commodity $commodity, int $amount): void
    {
        $this->storageManager->upperStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }

    #[Override]
    public function lowerStorage(Commodity $commodity, int $amount): void
    {
        $this->storageManager->lowerStorage(
            $this->colony,
            $commodity,
            $amount
        );
    }
}
