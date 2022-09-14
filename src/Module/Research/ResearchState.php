<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Research\ResearchEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;
use Stu\Orm\Repository\UserAwardRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private CrewCreatorInterface $crewCreator;

    private ShipCreatorInterface $shipCreator;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private EntityManagerInterface $entityManager;

    private AwardRepositoryInterface $awardRepository;

    private UserAwardRepositoryInterface $userAwardRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        CrewCreatorInterface $crewCreator,
        ShipCreatorInterface $shipCreator,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        EntityManagerInterface $entityManager,
        AwardRepositoryInterface $awardRepository,
        UserAwardRepositoryInterface $userAwardRepository
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->crewCreator = $crewCreator;
        $this->shipCreator = $shipCreator;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->entityManager = $entityManager;
        $this->awardRepository = $awardRepository;
        $this->userAwardRepository = $userAwardRepository;
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

        $this->createRewardShip($state);
        $this->createDatabaseEntries($state);
        $this->createShipRumpEntries($state);
        $this->createAward($state);
    }

    private function createRewardShip(ResearchedInterface $state): void
    {
        if ($state->getResearch()->getRewardBuildplan() === null) {
            return;
        }

        $userColonies = $this->colonyRepository->getOrderedListByUser($state->getUser());

        if (empty($userColonies)) {
            return;
        }

        $userId = $state->getUser()->getId();
        $plan = $state->getResearch()->getRewardBuildplan();
        $ship = $this->shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId(), current($userColonies));

        $ship->setEps($ship->getTheoreticalMaxEps());
        $ship->setReactorLoad($ship->getReactorCapacity());
        $ship->setEBatt($ship->getMaxEBatt());

        if ($plan->getCrew() > 0) {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
        }

        $this->shipRepository->save($ship);

        for ($j = 1; $j <= $plan->getCrew(); $j++) {
            $this->crewCreator->create($userId);
        }
        $this->entityManager->flush();
        $this->crewCreator->createShipCrew($ship);

        $txt = sprintf(_("Als Belohnung für den Abschluss der Forschung wurde dir ein Schiff vom Typ %s überstellt"), $plan->getRump()->getName());

        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $userId,
            $txt,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );
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

    private function createAward(ResearchedInterface $state): void
    {
        $user = $state->getUser();

        if (
            $state->getResearch()->getId() ===
            (ResearchEnum::RESEARCH_OFFSET_CONSTRUCTION + $user->getFaction()->getId())
        ) {
            $award = $this->awardRepository->find(UserAwardEnum::RESEARCHED_STATIONS);

            $userAward = $this->userAwardRepository->prototype();
            $userAward->setUser($user);
            $userAward->setAward($award);

            $this->userAwardRepository->save($userAward);
        }
    }
}
