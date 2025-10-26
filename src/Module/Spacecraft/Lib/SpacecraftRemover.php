<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class SpacecraftRemover implements SpacecraftRemoverInterface
{
    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private StorageRepositoryInterface $storageRepository,
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private ClearTorpedoInterface $clearTorpedo,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private SpacecraftShutdownInterface $spacecraftShutdown,
    ) {}

    private function resetTrackerDevices(int $shipId): void
    {
        foreach ($this->shipSystemRepository->getTrackingShipSystems($shipId) as $system) {
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($system->getSpacecraft());

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACKER, true);
        }
    }

    #[\Override]
    public function remove(Spacecraft $spacecraft, ?bool $truncateCrew = false): void
    {
        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

        $this->spacecraftShutdown->shutdown($wrapper, true);
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::NONE);

        //both sides have to be cleared, foreign key violation
        if ($spacecraft->isTractoring()) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
        } else {
            $tractoringShipWrapper = $wrapper instanceof ShipWrapperInterface ? $wrapper->getTractoringSpacecraftWrapper() : null;
            if ($tractoringShipWrapper !== null) {
                $this->spacecraftSystemManager->deactivate($tractoringShipWrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
            }
        }

        foreach ($spacecraft->getStorage() as $item) {
            $this->storageRepository->delete($item);
        }

        // delete torpedo storage
        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        if ($truncateCrew) {
            $crewArray = [];
            foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
                $crewArray[] = $shipCrew->getCrew();
            }

            $this->shipCrewRepository->truncateBySpacecraft($spacecraft);

            foreach ($crewArray as $crew) {
                $this->crewRepository->delete($crew);
            }

            $spacecraft->getCrewAssignments()->clear();
        }

        // reset tracker devices
        $this->resetTrackerDevices($spacecraft->getId());

        foreach ($spacecraft->getSystems() as $shipSystem) {
            $this->shipSystemRepository->delete($shipSystem);
        }

        $this->spacecraftRepository->delete($spacecraft);
    }
}
