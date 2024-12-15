<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use InvalidArgumentException;
use Override;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CrewLimitations implements ManagerComponentInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private UserRepositoryInterface $userRepository,
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private CrewLimitCalculatorInterface $crewLimitCalculator
    ) {}

    #[Override]
    public function work(): void
    {
        $userList = $this->userRepository->getNonNpcList();

        foreach ($userList as $user) {
            //only handle user that are not on vacation
            if ($user->isVacationRequestOldEnough()) {
                continue;
            }

            $userId = $user->getId();

            $crewLimit = $this->crewLimitCalculator->getGlobalCrewLimit($user);
            $crewOnColonies = $this->shipCrewRepository->getAmountByUserOnColonies($user->getId());
            $crewOnShips = $this->shipCrewRepository->getAmountByUserOnShips($user);
            $crewAtTradeposts = $this->shipCrewRepository->getAmountByUserAtTradeposts($user);

            $crewToQuit = max(0, $crewOnColonies + $crewOnShips + $crewAtTradeposts - $crewLimit);

            //mutiny order: colonies, ships, tradeposts, escape pods
            if ($crewToQuit > 0 && $crewOnColonies > 0) {
                $crewToQuit -= $this->letColonyAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0 && $crewOnShips > 0) {
                $crewToQuit -= $this->letShipAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0 && $crewAtTradeposts > 0) {
                $crewToQuit -= $this->letTradepostAssignmentsQuit($userId, $crewToQuit);
            }
            if ($crewToQuit > 0) {
                $this->letEscapePodAssignmentsQuit($userId, $crewToQuit);
            }
        }
    }

    private function letColonyAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserAtColonies($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d Crewman ihren Dienst auf deinen Kolonien quittiert'), $amount);
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY
            );
        }

        return $amount;
    }

    private function letTradepostAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserAtTradeposts($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d deiner Crewman auf Handelsposten ihren Dienst quittiert'), $amount);
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }

        return $amount;
    }

    private function letEscapePodAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $amount = 0;

        foreach ($this->shipCrewRepository->getByUserOnEscapePods($userId) as $crewAssignment) {
            if ($amount === $crewToQuit) {
                break;
            }

            $amount++;
            $this->crewRepository->delete($crewAssignment->getCrew());
        }

        if ($amount > 0) {
            $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d deiner Crewman auf Fluchtkapseln ihren Dienst quittiert'), $amount);
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $userId,
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }

        return $amount;
    }

    private function letShipAssignmentsQuit(int $userId, int $crewToQuit): int
    {
        $wipedShipsIds = [];
        $amount = 0;

        $wipedShipIds = [];

        while ($amount < $crewToQuit) {
            $randomShipId = $this->spacecraftRepository->getRandomSpacecraftIdWithCrewByUser($userId);

            //if no more ships available
            if ($randomShipId === null) {
                break;
            }

            //if ship already wiped, go to next
            if (in_array($randomShipId, $wipedShipIds)) {
                continue;
            }

            //wipe ship crew
            $wipedShipsIds[] = $randomShipId;
            $amount += $this->letCrewQuit($randomShipId, $userId);
        }

        return $amount;
    }

    private function letCrewQuit(int $randomShipId, int $userId): int
    {
        $randomShip = $this->spacecraftRepository->find($randomShipId);

        if ($randomShip === null) {
            throw new InvalidArgumentException('randomShipId should exist');
        }

        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($randomShip);
        $doAlertRedCheck = $randomShip->getWarpDriveState() || $randomShip->getCloakState();
        //deactivate ship
        $this->spacecraftSystemManager->deactivateAll($wrapper);
        $randomShip->setAlertStateGreen();

        $this->spacecraftRepository->save($randomShip);

        $crewArray = [];
        foreach ($randomShip->getCrewAssignments() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
        }
        $randomShip->getCrewAssignments()->clear();

        //remove crew
        $this->shipCrewRepository->truncateByShip($randomShipId);
        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }

        $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits hat die Crew der %s gemeutert und das Schiff verlassen'), $randomShip->getName());
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $userId,
            $msg,
            $randomShip->isStation() ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        //do alert red stuff
        if ($doAlertRedCheck) {
            $this->alertReactionFacade->doItAll($wrapper, new InformationWrapper());
        }

        return count($crewArray);
    }
}
