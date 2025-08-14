<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use BadMethodCallException;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

class ManagerProviderStation implements ManagerProviderInterface
{
    public function __construct(
        private StationWrapperInterface $wrapper,
        private CrewCreatorInterface $crewCreator,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function getUser(): User
    {
        return $this->wrapper->get()->getUser();
    }

    #[Override]
    public function getEps(): int
    {
        $eps = $this->wrapper->getEpsSystemData();

        if ($eps === null) {
            return 0;
        }

        return $eps->getEps();
    }

    #[Override]
    public function lowerEps(int $amount): ManagerProviderInterface
    {
        $eps = $this->wrapper->getEpsSystemData();

        if ($eps === null) {
            throw new BadMethodCallException('can not lower eps without eps system');
        }

        $eps->lowerEps($amount)->update();

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        $station = $this->wrapper->get();

        return sprintf(
            '%s %s',
            $station->getRump()->getName(),
            $station->getName(),
        );
    }

    #[Override]
    public function getSectorString(): string
    {
        return $this->wrapper->get()->getSectorString();
    }

    #[Override]
    public function getFreeCrewAmount(): int
    {
        return $this->wrapper->get()->getExcessCrewCount();
    }

    #[Override]
    public function addCrewAssignment(Spacecraft $spacecraft, int $amount): void
    {
        $this->crewCreator->createCrewAssignments($spacecraft, $this->wrapper->get(), $amount);
    }

    #[Override]
    public function getFreeCrewStorage(): int
    {
        $station = $this->wrapper->get();

        return $this->troopTransferUtility->getFreeQuarters($station);
    }

    #[Override]
    public function addCrewAssignments(array $crewAssignments): void
    {
        $station = $this->wrapper->get();

        foreach ($crewAssignments as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $station);
        }
    }

    #[Override]
    public function getStorage(): Collection
    {
        return $this->wrapper->get()->getStorage();
    }

    #[Override]
    public function upperStorage(Commodity $commodity, int $amount): void
    {
        $this->storageManager->upperStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }

    #[Override]
    public function lowerStorage(Commodity $commodity, int $amount): void
    {
        $this->storageManager->lowerStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }
}
