<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Module\Communication\Lib\PrivateMessageSender;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    private $researchedRepository;

    private $shipRumpUserRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
    }

    public function finish(ResearchedInterface $state): void
    {
        $state->setActive(0);
        $state->setFinished(time());

        PrivateMessageSender::sendPM(
            USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '" . $state->getResearch()->getName() . "' wurde abgeschlossen",
            PM_SPECIAL_COLONY
        );

        $this->createDatabaseEntries($state);
        $this->createShipRumpEntries($state);
    }

    private function createShipRumpEntries(ResearchedInterface $state): void
    {
        $shipRumpId = $state->getResearch()->getRumpId();
        if (!$shipRumpId) {
            return;
        }
        if ($this->shipRumpUserRepository->isAvailableForUser($shipRumpId, $state->getUserId()) === true) {
            return;
        }
        $entry = $this->shipRumpUserRepository->prototype();
        $entry->setUserId($state->getUserId());
        $entry->setShipRumpId($shipRumpId);

        $this->shipRumpUserRepository->save($entry);
    }

    private function createDatabaseEntries(ResearchedInterface $state): void
    {
        foreach ($state->getResearch()->getDatabaseEntryIds() as $entry) {
            databaseScan($entry, $state->getUserId());
        }
    }

    public function advance(ResearchedInterface $state, int $amount): void
    {
        if ($state->getActive() - $amount <= 0) {
            $this->finish($state);
        } else {
            $state->setActive($state->getActive() - $amount);
        }

        $this->researchedRepository->save($state);
    }
}