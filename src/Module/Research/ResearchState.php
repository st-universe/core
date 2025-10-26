<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    public function __construct(
        private readonly ResearchedRepositoryInterface $researchedRepository,
        private readonly ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        private readonly PrivateMessageSenderInterface $privateMessageSender,
        private readonly CreateDatabaseEntryInterface $createDatabaseEntry,
        private readonly ShipCreatorInterface $shipCreator,
        private readonly CreateUserAwardInterface $createUserAward,
    ) {}

    #[\Override]
    public function advance(Researched $state, int $amount): int
    {
        $active = $state->getActive();

        if ($amount >= $active) {
            $this->finish($state);
        } else {
            $state->setActive($active - $amount);
        }

        $this->researchedRepository->save($state);

        return max(0, $amount - $active);
    }

    #[\Override]
    public function finish(Researched $state): void
    {
        $state->setActive(0);
        $state->setFinished(time());

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '" . $state->getResearch()->getName() . "' wurde abgeschlossen",
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $this->createRewardShip($state);
        $this->createDatabaseEntries($state);
        $this->createShipRumpEntries($state);
        $this->checkForAward($state);
    }

    private function createRewardShip(Researched $state): void
    {
        if ($state->getResearch()->getRewardBuildplan() === null) {
            return;
        }

        $userColonies = $state->getUser()->getColonies();

        if ($userColonies->isEmpty()) {
            return;
        }

        $userId = $state->getUser()->getId();
        $plan = $state->getResearch()->getRewardBuildplan();
        /** @var Colony */
        $colony = $userColonies->first();
        $this->shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId())
            ->setLocation($colony->getStarsystemMap())
            ->maxOutSystems()
            ->createCrew()
            ->finishConfiguration();

        $txt = sprintf(_("Als Belohnung für den Abschluss der Forschung wurde dir ein Schiff vom Typ %s überstellt"), $plan->getRump()->getName());

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $userId,
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    private function createShipRumpEntries(Researched $state): void
    {
        $rumpId = $state->getResearch()->getRumpId();
        if ($rumpId === 0) {
            return;
        }
        if ($this->shipRumpUserRepository->isAvailableForUser($rumpId, $state->getUserId()) === true) {
            return;
        }
        $entry = $this->shipRumpUserRepository->prototype();
        $entry->setUser($state->getUser());
        $entry->setRumpId($rumpId);

        $this->shipRumpUserRepository->save($entry);
    }

    private function createDatabaseEntries(Researched $state): void
    {
        foreach ($state->getResearch()->getDatabaseEntryIds() as $entry) {
            $this->createDatabaseEntry->createDatabaseEntryForUser($state->getUser(), $entry);
        }
    }

    private function checkForAward(Researched $state): void
    {
        $user = $state->getUser();
        $award = $state->getResearch()->getAward();

        // nothing to do
        if ($award === null) {
            return;
        }

        // create user award
        $this->createUserAward->createAwardForUser($user, $award);
    }
}
