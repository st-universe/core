<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    private $researchedRepository;

    private $shipRumpUserRepository;

    private $privateMessageSender;

    private $createDatabaseEntry;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreateDatabaseEntryInterface $createDatabaseEntry
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->createDatabaseEntry = $createDatabaseEntry;
    }

    public function finish(ResearchedInterface $state): void
    {
        $state->setActive(0);
        $state->setFinished(time());

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '" . $state->getResearch()->getName() . "' wurde abgeschlossen",
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
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
        $entry->setUser($state->getUser());
        $entry->setShipRumpId($shipRumpId);

        $this->shipRumpUserRepository->save($entry);
    }

    private function createDatabaseEntries(ResearchedInterface $state): void
    {
        foreach ($state->getResearch()->getDatabaseEntryIds() as $entry) {
            $this->createDatabaseEntry->createDatabaseEntryForUser($state->getUser(), $entry);
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
