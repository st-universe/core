<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Auxiliary\ShipShutdownInterface;
use Stu\Module\Ship\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShipRemover implements ShipRemoverInterface
{
    public function __construct(
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private StorageRepositoryInterface $storageRepository,
        private CrewRepositoryInterface $crewRepository,
        private ShipCrewRepositoryInterface $shipCrewRepository,
        private ShipRepositoryInterface $shipRepository,
        private ShipSystemManagerInterface $shipSystemManager,
        private ClearTorpedoInterface $clearTorpedo,
        private ShipStateChangerInterface $shipStateChanger,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private ShipShutdownInterface $shipShutdown,
    ) {
    }

    private function resetTrackerDevices(int $shipId): void
    {
        foreach ($this->shipSystemRepository->getTrackingShipSystems($shipId) as $system) {
            $wrapper = $this->shipWrapperFactory->wrapShip($system->getShip());

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);
        }
    }

    public function remove(ShipInterface $ship, ?bool $truncateCrew = false): void
    {
        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        $this->shipShutdown->shutdown($wrapper, true);
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_NONE);

        //both sides have to be cleared, foreign key violation
        if ($ship->isTractoring()) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
        } else {
            $tractoringShipWrapper = $wrapper->getTractoringShipWrapper();
            if ($tractoringShipWrapper !== null) {
                $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            }
        }

        foreach ($ship->getStorage() as $item) {
            $this->storageRepository->delete($item);
        }

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        if ($truncateCrew) {
            $crewArray = [];
            foreach ($ship->getCrewAssignments() as $shipCrew) {
                $crewArray[] = $shipCrew->getCrew();
            }

            $this->shipCrewRepository->truncateByShip($ship->getId());

            foreach ($crewArray as $crew) {
                $this->crewRepository->delete($crew);
            }

            $ship->getCrewAssignments()->clear();
        }

        // reset tracker devices
        $this->resetTrackerDevices($ship->getId());

        foreach ($ship->getSystems() as $shipSystem) {
            $this->shipSystemRepository->delete($shipSystem);
        }

        $this->shipRepository->delete($ship);
    }
}
