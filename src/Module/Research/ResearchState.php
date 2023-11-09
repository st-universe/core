<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;

final class ResearchState implements ResearchStateInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private ShipRumpUserRepositoryInterface $shipRumpUserRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private CrewCreatorInterface $crewCreator;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private EntityManagerInterface $entityManager;

    private CreateUserAwardInterface $createUserAward;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        CrewCreatorInterface $crewCreator,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CreateUserAwardInterface $createUserAward,
        EntityManagerInterface $entityManager
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->shipRumpUserRepository = $shipRumpUserRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->crewCreator = $crewCreator;
        $this->shipCreator = $shipCreator;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->entityManager = $entityManager;
        $this->createUserAward = $createUserAward;
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
            UserEnum::USER_NOONE,
            $state->getUser()->getId(),
            "Forschung '" . $state->getResearch()->getName() . "' wurde abgeschlossen",
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
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
        $wrapper = $this->shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId(), $colony);
        $ship = $wrapper->get();

        $reactor = $wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor->setLoad($reactor->getCapacity());
        }

        $eps = $wrapper->getEpsSystemData();

        if ($eps !== null) {
            $eps->setEps($eps->getTheoreticalMaxEps())
                ->setBattery($eps->getMaxBattery())->update();
        }

        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive !== null) {
            $warpdrive->setWarpDrive($warpdrive->getMaxWarpdrive())->update();
        }

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
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
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
