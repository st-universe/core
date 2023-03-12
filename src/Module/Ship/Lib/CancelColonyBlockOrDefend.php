<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CancelColonyBlockOrDefend implements CancelColonyBlockOrDefendInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function work(ShipInterface $ship, bool $isTraktor = false): array
    {
        $msg = [];

        $this->loggerUtil->log('A');
        $target = $isTraktor ? $ship->getTractoredShip() : $ship;
        $this->loggerUtil->log('B');

        if ($target === null || !$target->isFleetLeader()) {
            $this->loggerUtil->log('C');
            return $msg;
        }
        $this->loggerUtil->log('D');

        $fleet = $target->getFleet();

        if ($fleet->getDefendedColony() !== null) {
            $this->loggerUtil->log('E');
            $colony = $fleet->getDefendedColony();

            if ($isTraktor) {
                $this->privateMessageSender->send(
                    $ship->getUser()->getId(),
                    $target->getUser()->getId(),
                    sprintf(
                        _('Die %s wurde mit dem Traktorstrahl gezogen, daher hat die Flotte %s die Verteidigung der Kolonie %s eingestellt'),
                        $target->getName(),
                        $fleet->getName(),
                        $colony->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $colony->getUser()->getId(),
                sprintf(
                    _('Die Flotte %s hat von Spieler %s hat die Verteidigung der Kolonie %s aufgehoben'),
                    $fleet->getName(),
                    $fleet->getUser()->getName(),
                    $colony->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );

            $msg[] = sprintf(
                _('Die Flotte der %s hat die Verteidigung der Kolonie %s abgebrochen'),
                $target->getName(),
                $colony->getName()
            );
            $fleet->setDefendedColony(null);
            $this->fleetRepository->save($fleet);

            return $msg;
        }

        if ($fleet->getBlockedColony() !== null) {
            $this->loggerUtil->log('F');
            $colony = $fleet->getBlockedColony();

            if ($isTraktor) {
                $this->privateMessageSender->send(
                    $ship->getUser()->getId(),
                    $target->getUser()->getId(),
                    sprintf(
                        _('Die %s wurde mit dem Traktorstrahl gezogen, daher hat die Flotte %s die Blockade der Kolonie %s eingestellt'),
                        $target->getName(),
                        $fleet->getName(),
                        $colony->getName()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }

            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $colony->getUser()->getId(),
                sprintf(
                    _('Die Flotte %s hat von Spieler %s hat die Blockade der Kolonie %s aufgehoben'),
                    $fleet->getName(),
                    $fleet->getUser()->getName(),
                    $colony->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
            $msg[] = sprintf(
                _('Die Flotte der %s hat die Blockade der Kolonie %s abgebrochen'),
                $target->getName(),
                $colony->getName()
            );
            $fleet->setBlockedColony(null);
            $this->fleetRepository->save($fleet);

            return $msg;
        }

        return $msg;
    }
}
