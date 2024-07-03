<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    public function __construct(private ResearchedRepositoryInterface $researchedRepository, private ShipRumpUserRepositoryInterface $shipRumpUserRepository, private PrivateMessageSenderInterface $privateMessageSender, private CreateDatabaseEntryInterface $createDatabaseEntry, private CrewCreatorInterface $crewCreator, private ShipCreatorInterface $shipCreator, private ShipRepositoryInterface $shipRepository, private ShipSystemManagerInterface $shipSystemManager, private CreateUserAwardInterface $createUserAward, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function advance(ResearchedInterface $state, int $amount): int
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

    #[Override]
    public function finish(ResearchedInterface $state): void
    {
        $state->setActive(0);
        $state->setFinished(time());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '" . $state->getResearch()->getName() . "' wurde abgeschlossen",
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $this->createRewardShip($state);
        $this->createDatabaseEntries($state);
        $this->createShipRumpEntries($state);
        $this->checkForAward($state);
    }

    private function createRewardShip(ResearchedInterface $state): void
    {
        if ($state->getResearch()->getRewardBuildplan() === null) {
            return;
        }

        $userColonies = $state->getUser()->getColonies()->toArray();

        if ($userColonies === []) {
            return;
        }

        $userId = $state->getUser()->getId();
        $plan = $state->getResearch()->getRewardBuildplan();
        $colony = current($userColonies);
        $wrapper = $this->shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId(), $colony)
            ->maxOutSystems()
            ->finishConfiguration();
        $ship = $wrapper->get();

        if ($plan->getCrew() > 0) {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
        }

        $this->shipRepository->save($ship);

        for ($j = 1; $j <= $plan->getCrew(); $j++) {
            $this->crewCreator->create($userId, $colony);
        }
        $this->entityManager->flush();
        $this->crewCreator->createShipCrew($ship, $colony);

        $txt = sprintf(_("Als Belohnung für den Abschluss der Forschung wurde dir ein Schiff vom Typ %s überstellt"), $plan->getRump()->getName());

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $userId,
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );
    }

    private function createShipRumpEntries(ResearchedInterface $state): void
    {
        $shipRumpId = $state->getResearch()->getRumpId();
        if ($shipRumpId === 0) {
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

    private function checkForAward(ResearchedInterface $state): void
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
