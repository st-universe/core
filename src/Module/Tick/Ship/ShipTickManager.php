<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipTickManager implements ShipTickManagerInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private CrewRepositoryInterface $crewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private AlertRedHelperInterface $alertRedHelper;

    private EntityManagerInterface $entityManager;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        CrewRepositoryInterface $crewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipSystemManagerInterface $shipSystemManager,
        AlertRedHelperInterface $alertRedHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->crewRepository = $crewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->alertRedHelper = $alertRedHelper;
        $this->entityManager = $entityManager;
    }

    public function work(): void
    {
        $this->checkForCrewLimitation();
        $this->removeEmptyEscapePods();

        foreach ($this->shipRepository->getPlayerShipsForTick() as $ship) {
            //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";

            //handle ship only if vacation mode not active
            if (!$ship->getUser()->isVacationRequestOldEnough()) {
                $this->shipTick->work($ship);
            }
        }
        $this->handleNPCShips();
        $this->lowerTrumfieldHull();
        $this->lowerStationConstructionHull();

        $this->entityManager->flush();
    }

    private function removeEmptyEscapePods(): void
    {
        foreach ($this->shipRepository->getEscapePods() as $ship) {
            if ($ship->getCrewCount() == 0) {
                $this->shipRemover->remove($ship);
            }
        }
    }

    private function checkForCrewLimitation(): void
    {
        $userList = $this->userRepository->getNonNpcList();

        foreach ($userList as $user) {
            //only handle user that are not on vacation
            if ($user->isVacationRequestOldEnough()) {
                continue;
            }

            $crewLimit = $user->getGlobalCrewLimit();
            $crewOnShips = $this->shipCrewRepository->getAmountByUser($user->getId());
            $freeCrewCount = $this->crewRepository->getFreeAmountByUser($user->getId());

            if (($crewOnShips + $freeCrewCount) > $crewLimit) {
                if ($freeCrewCount > 0) {
                    $deleteAmount = min($crewOnShips + $freeCrewCount - $crewLimit, $freeCrewCount);

                    for ($i = 0; $i < $deleteAmount; $i++) {

                        $crew = $this->crewRepository->getFreeByUser($user->getId());
                        $this->crewRepository->delete($crew);
                    }

                    $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d freie Crewman ihren Dienst quittiert'), $deleteAmount);
                    $this->privateMessageSender->send(
                        GameEnum::USER_NOONE,
                        (int) $user->getId(),
                        $msg,
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
                    );
                } else {
                    $crewToQuit = $crewOnShips - $crewLimit;

                    while ($crewToQuit > 0) {
                        $quitAmount = $this->letCrewQuit($user);

                        if ($quitAmount === null) {
                            break;
                        }

                        $crewToQuit -= $quitAmount;
                    }
                }
            }
        }
    }

    private function letCrewQuit(UserInterface $user): ?int
    {
        $randomShipId = $this->shipRepository->getRandomShipIdWithCrewByUser($user->getId());

        if ($randomShipId === null) {
            return null;
        }

        $randomShip = $this->shipRepository->find($randomShipId);
        $doAlertRedCheck = $randomShip->getWarpState() || $randomShip->getCloakState();
        //deactivate ship
        $this->shipSystemManager->deactivateAll($randomShip);
        $randomShip->setAlertState(ShipAlertStateEnum::ALERT_GREEN);

        $this->shipRepository->save($randomShip);

        $crewArray = [];
        foreach ($randomShip->getCrewlist() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
        }
        $randomShip->getCrewlist()->clear();

        //remove crew
        $this->shipCrewRepository->truncateByShip($randomShipId);
        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }

        $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits hat die Crew der %s gemeutert und das Schiff verlassen'), $randomShip->getName());
        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            (int) $user->getId(),
            $msg,
            $randomShip->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        //do alert red stuff
        if ($doAlertRedCheck) {
            $this->doAlertRedCheck($randomShip);
        }

        return count($crewArray);
    }

    private function doAlertRedCheck($ship): void
    {
        $informations = [];

        //Alarm-Rot check
        $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($ship, $informations);
        shuffle($shipsToShuffle);
        foreach ($shipsToShuffle as $alertShip) {
            $this->alertRedHelper->performAttackCycle($alertShip, $ship, $informations);
        }
    }

    private function lowerTrumfieldHull(): void
    {
        foreach ($this->shipRepository->getDebrisFields() as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHuell() <= $lower) {

                $msg = sprintf(_('Dein Konstrukt bei %s war zu lange ungenutzt und ist daher zerfallen'), $ship->getSectorString());
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    $msg,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                );

                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHuell() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function lowerStationConstructionHull(): void
    {
        foreach ($this->shipRepository->getStationConstructions() as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHuell() <= $lower) {
                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->setHuell($ship->getHuell() - $lower);

            $this->shipRepository->save($ship);
        }
    }

    private function handleNPCShips(): void
    {
        // @todo
        foreach ($this->shipRepository->getNpcShipsForTick() as $ship) {
            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)) {
                $eps = (int) ceil($ship->getReactorOutput() - $ship->getEpsUsage());
                if ($eps + $ship->getEps() > $ship->getMaxEps()) {
                    $eps = $ship->getMaxEps() - $ship->getEps();
                }
                $ship->setEps($ship->getEps() + $eps);
            } else {
                $eps = (int) ceil($ship->getTheoreticalMaxEps() / 10);
                if ($eps + $ship->getEps() > $ship->getTheoreticalMaxEps()) {
                    $eps = $ship->getTheoreticalMaxEps() - $ship->getEps();
                }
                $ship->setEps($ship->getEps() + $eps);
            }

            $this->shipRepository->save($ship);
        }
    }
}
