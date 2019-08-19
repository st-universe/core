<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use PM;
use RumpUser;
use RumpUserData;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{

    private $researchedRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository
    )
    {
        $this->researchedRepository = $researchedRepository;
    }

    public function finish(ResearchedInterface $state): void {
        $state->setActive(0);
        $state->setFinished(time());

        PM::sendPM(
            USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '".$state->getResearch()->getName()."' wurde abgeschlossen",
            PM_SPECIAL_COLONY
        );

        $this->createDatabaseEntries($state);
        $this->createShipRumpEntries($state);
    }

    private function createShipRumpEntries(ResearchedInterface $state): void {
        if (!$state->getResearch()->getRumpId()) {
            return;
        }
        if (RumpUser::countInstances('user_id='.$state->getUserId().' AND rump_id='.$state->getResearch()->getRumpId()) > 0) {
            return;
        }
        $entry = new RumpUserData();
        $entry->setUserId($state->getUserId());
        $entry->setRumpId($state->getResearch()->getRumpId());
        $entry->save();
    }

    private function createDatabaseEntries(ResearchedInterface $state): void {
        foreach ($state->getResearch()->getDatabaseEntryIds() as $entry) {
            databaseScan($entry, $state->getUserId());
        }
    }

    public function advance(ResearchedInterface $state, int $amount): void {
        if ($state->getActive() - $amount <= 0) {
            $this->finish($state);
        } else {
            $state->setActive($state->getActive() - $amount);
        }

        $this->researchedRepository->save($state);
    }
}