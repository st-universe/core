<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

final class ShipTickManager implements ShipTickManagerInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;
    
    private ShipRemoverInterface $shipRemover;

    private ShipTickInterface $shipTick;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private CrewRepositoryInterface $crewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        CrewRepositoryInterface $crewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->crewRepository = $crewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
    }

    public function work(): void
    {
        $this->checkForCrewLimitation();
        $this->removeEmptyEscapePods();
        
        foreach ($this->shipRepository->getPlayerShipsForTick() as $ship) {
            //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";

            //handle ship only if vacation mode not active
            if (!$ship->getUser()->isVacationRequestOldEnough())
            {
                $this->shipTick->work($ship);
            }

        }
        $this->handleNPCShips();
        $this->lowerTrumfieldHuell();
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

        foreach ($userList as $user)
        {
            //only handle user that are not on vacation
            if ($user->isVacationRequestOldEnough())
            {
                continue;
            }

            $crewLimit = $user->getGlobalCrewLimit();
            $crewOnShips = $this->shipCrewRepository->getAmountByUser();
            $freeCrewCount = $this->crewRepository->getFreeAmountByUser($user->getId());

            if (($crewOnShips + $freeCrewCount) > $crewLimit)
            {
                if ($freeCrewCount > 0)
                {
                    $deleteAmount = min($crewOnShips - $crewLimit, $freeCrewCount);
                    
                    for ($i = 0; $i < $deleteAmount; $i++) {

                        $crew = $this->crewRepository->getFreeByUser($user->getId());
                        $this->crewRepository->delete($crew);
                    }

                    $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits haben %d freie Crewman ihren Dienst quittiert'), $deleteAmount);
                    $this->privateMessageSender->send(GameEnum::USER_NOONE, (int)$user->getId(), $msg,
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY);
                } else {
                    $randomShip = $this->shipRepository->getRandomShipWithCrewByUser($user->getId());

                    if ($randomShip == null)
                    {
                        continue;
                    }
                    $this->shipCrewRepository->truncateByShip($randomShip->getId());
                    $this->shipSystemManager->deactivateAll($randomShip);
                    $randomShip->setAlertState(ShipAlertStateEnum::ALERT_GREEN);

                    $this->shipRepository->save($randomShip);

                    $msg = sprintf(_('Wegen Überschreitung des globalen Crewlimits hat die Crew der %s gemeutert und das Schiff verlassen'), $randomShip->getName());
                    $this->privateMessageSender->send(GameEnum::USER_NOONE, (int)$user->getId(), $msg,
                        PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);
                }
            }

        }
    }

    private function lowerTrumfieldHuell(): void
    {
        foreach ($this->shipRepository->getDebrisFields() as $ship) {
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
            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_EPS))
            {
                $eps = (int)ceil($ship->getMaxEps() / 10);
                if ($eps + $ship->getEps() > $ship->getMaxEps()) {
                    $eps = $ship->getMaxEps() - $ship->getEps();
                }
                $ship->setEps($ship->getEps() + $eps);
            }
            else
            {
                $eps = (int)ceil($ship->getTheoreticalMaxEps() / 10);
                if ($eps + $ship->getEps() > $ship->getTheoreticalMaxEps()) {
                    $eps = $ship->getTheoreticalMaxEps() - $ship->getEps();
                }
                $ship->setEps($ship->getEps() + $eps);
            }

            $this->shipRepository->save($ship);
        }
    }
}
