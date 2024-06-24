<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonyResetter implements ColonyResetterInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    private StorageRepositoryInterface $storageRepository;

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private FleetRepositoryInterface $fleetRepository;

    private CrewRepositoryInterface $crewRepository;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ColonySandboxRepositoryInterface $colonySandboxRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        StorageRepositoryInterface $storageRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        FleetRepositoryInterface $fleetRepository,
        CrewRepositoryInterface $crewRepository,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ColonySandboxRepositoryInterface $colonySandboxRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->storageRepository = $storageRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->fleetRepository = $fleetRepository;
        $this->crewRepository = $crewRepository;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->colonySandboxRepository = $colonySandboxRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function reset(
        ColonyInterface $colony,
        bool $sendMessage = true
    ): void {
        $this->resetBlockers($colony, $sendMessage);
        $this->resetDefenders($colony, $sendMessage);
        $this->resetCrew($colony);
        $this->resetCrewTraining($colony);

        $colony->setEps(0)
            ->setMaxEps(0)
            ->setMaxStorage(0)
            ->setWorkers(0)
            ->setWorkless(0)
            ->setMaxBev(0)
            ->setTorpedo(null)
            ->setShieldFrequency(0)
            ->setShields(0)
            ->setImmigrationstate(true)
            ->setPopulationlimit(0)
            ->setUser($this->userRepository->getFallbackUser())
            ->setName('');

        $this->colonyRepository->save($colony);

        $this->storageRepository->truncateByColony($colony);

        foreach ($this->colonyTerraformingRepository->getByColony([$colony]) as $fieldTerraforming) {
            $this->colonyTerraformingRepository->delete($fieldTerraforming);
        }

        $this->colonyShipQueueRepository->truncateByColony($colony);
        $this->planetFieldRepository->truncateByColony($colony);
        $this->colonySandboxRepository->truncateByColony($colony);
    }

    private function resetBlockers(ColonyInterface $colony, bool $sendMessage = true): void
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

    private function resetDefenders(ColonyInterface $colony, bool $sendMessage = true): void
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

    private function resetCrew(ColonyInterface $colony): void
    {
        foreach ($colony->getCrewAssignments() as $crewAssignment) {
            $this->shipCrewRepository->delete($crewAssignment);
            $this->crewRepository->delete($crewAssignment->getCrew());
        }
    }

    private function resetCrewTraining(ColonyInterface $colony): void
    {
        $this->crewTrainingRepository->truncateByColony($colony);
    }

    private function sendMessage(ColonyInterface $colony, FleetInterface $fleet, bool $isDefending): void
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
            UserEnum::USER_NOONE,
            $fleet->getUserId(),
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }
}
