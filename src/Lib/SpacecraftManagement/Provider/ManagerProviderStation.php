<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class ManagerProviderStation implements ManagerProviderInterface
{
    public function __construct(
        private StationWrapperInterface $wrapper,
        private CrewCreatorInterface $crewCreator,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function getUser(): UserInterface
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
            throw new RuntimeException('can not lower eps without eps system');
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
    public function addCrewAssignment(SpacecraftInterface $spacecraft, int $amount): void
    {
        $this->crewCreator->createCrewAssignment($spacecraft, $this->wrapper->get(), $amount);
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
    public function upperStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->storageManager->upperStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }

    #[Override]
    public function lowerStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->storageManager->lowerStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }
}
