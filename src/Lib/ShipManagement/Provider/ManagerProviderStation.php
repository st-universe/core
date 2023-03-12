<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

class ManagerProviderStation implements ManagerProviderInterface
{
    private ShipWrapperInterface $wrapper;

    private CrewCreatorInterface $crewCreator;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        ShipWrapperInterface $wrapper,
        CrewCreatorInterface $crewCreator,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->wrapper = $wrapper;
        $this->crewCreator = $crewCreator;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function getUser(): UserInterface
    {
        return $this->wrapper->get()->getUser();
    }

    public function getEps(): int
    {
        $eps = $this->wrapper->getEpsSystemData();

        if ($eps === null) {
            return 0;
        }

        return $eps->getEps();
    }

    public function lowerEps(int $amount): ManagerProviderInterface
    {
        $eps = $this->wrapper->getEpsSystemData();

        if ($eps === null) {
            throw new RuntimeException('can not lower eps without eps system');
        }

        $eps->setEps($eps->getEps() - $amount)->update();

        return $this;
    }

    public function getName(): string
    {
        $station = $this->wrapper->get();

        return sprintf(
            '%s %s',
            $station->getRump()->getName(),
            $station->getName(),
        );
    }

    public function getSectorString(): string
    {
        return $this->wrapper->get()->getSectorString();
    }

    public function getFreeCrewAmount(): int
    {
        return $this->wrapper->get()->getExcessCrewCount();
    }

    public function createShipCrew(ShipInterface $ship): void
    {
        $this->crewCreator->createShipCrew($ship, null, $this->wrapper->get());
    }

    public function isAbleToStoreCrew(int $amount): bool
    {
        $station = $this->wrapper->get();

        $freeAssignmentCount = $this->shipCrewCalculator->getMaxCrewCountByShip($station) - $station->getCrewCount();

        return $freeAssignmentCount >= $amount;
    }

    public function addCrewAssignments(Collection $crewAssignments): void
    {
        $station = $this->wrapper->get();

        foreach ($crewAssignments as $shipCrew) {
            $shipCrew->setShip($station);
            $shipCrew->setSlot(null);
            $station->getCrewlist()->add($shipCrew);
            $this->shipCrewRepository->save($shipCrew);
        }
    }

    public function getStorage(): Collection
    {
        return $this->wrapper->get()->getStorage();
    }

    public function upperStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->shipStorageManager->upperStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }

    public function lowerStorage(CommodityInterface $commodity, int $amount): void
    {
        $this->shipStorageManager->lowerStorage(
            $this->wrapper->get(),
            $commodity,
            $amount
        );
    }
}
