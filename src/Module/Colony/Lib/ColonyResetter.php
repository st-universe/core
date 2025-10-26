<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonyResetter implements ColonyResetterInterface
{
    public function __construct(
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly StorageRepositoryInterface $storageRepository,
        private readonly ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        private readonly ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly FleetRepositoryInterface $fleetRepository,
        private readonly CrewRepositoryInterface $crewRepository,
        private readonly CrewTrainingRepositoryInterface $crewTrainingRepository,
        private readonly CrewAssignmentRepositoryInterface $shipCrewRepository,
        private readonly ColonySandboxRepositoryInterface $colonySandboxRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
    public function reset(
        Colony $colony,
        bool $sendMessage = true
    ): void {
        $this->resetBlockers($colony, $sendMessage);
        $this->resetDefenders($colony, $sendMessage);
        $this->resetCrew($colony);
        $this->resetCrewTraining($colony);

        $colony
            ->setUser($this->userRepository->getFallbackUser())
            ->setName('')
            ->getChangeable()
            ->setEps(0)
            ->setMaxEps(0)
            ->setMaxStorage(0)
            ->setWorkers(0)
            ->setWorkless(0)
            ->setMaxBev(0)
            ->setTorpedo(null)
            ->setShieldFrequency(0)
            ->setShields(0)
            ->setImmigrationstate(true)
            ->setPopulationlimit(0);

        $this->colonyRepository->save($colony);

        $this->storageRepository->truncateByColony($colony);

        foreach ($this->colonyTerraformingRepository->getByColony([$colony]) as $fieldTerraforming) {
            $this->colonyTerraformingRepository->delete($fieldTerraforming);
        }

        $this->colonyShipQueueRepository->truncateByColony($colony);
        $this->planetFieldRepository->truncateByColony($colony);
        $this->colonySandboxRepository->truncateByColony($colony);
    }

    private function resetBlockers(Colony $colony, bool $sendMessage = true): void
    {
        foreach ($colony->getBlockers() as $blockerFleet) {
            if ($sendMessage) {
                $this->sendMessage($colony, $blockerFleet, false);
            }

            $blockerFleet->setBlockedColony(null);
            $this->fleetRepository->save($blockerFleet);
        }

        $colony->getBlockers()->clear();
    }

    private function resetDefenders(Colony $colony, bool $sendMessage = true): void
    {
        foreach ($colony->getDefenders() as $defenderFleet) {
            if ($sendMessage) {
                $this->sendMessage($colony, $defenderFleet, true);
            }

            $defenderFleet->setDefendedColony(null);
            $this->fleetRepository->save($defenderFleet);
        }

        $colony->getDefenders()->clear();
    }

    private function resetCrew(Colony $colony): void
    {
        foreach ($colony->getCrewAssignments() as $crewAssignment) {
            $this->shipCrewRepository->delete($crewAssignment);
            $this->crewRepository->delete($crewAssignment->getCrew());
        }
    }

    private function resetCrewTraining(Colony $colony): void
    {
        $this->crewTrainingRepository->truncateByColony($colony);
    }

    private function sendMessage(Colony $colony, Fleet $fleet, bool $isDefending): void
    {
        $txt = sprintf(
            'Der Spieler %s hat die Kolonie %s in Sektor %d|%d (%s System) verlassen. Deine Flotte %s hat die %s beendet.',
            $colony->getUser()->getName(),
            $colony->getName(),
            $colony->getSx(),
            $colony->getSy(),
            $colony->getSystem()->getName(),
            $fleet->getName(),
            $isDefending ? 'Verteidigung' : 'Blockade'
        );

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $fleet->getUserId(),
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }
}
