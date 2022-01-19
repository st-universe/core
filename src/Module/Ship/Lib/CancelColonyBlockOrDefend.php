<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class CancelColonyBlockOrDefend implements CancelColonyBlockOrDefendInterface
{
    private FleetRepositoryInterface $fleetRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(ShipInterface $ship): array
    {
        $msg = [];

        $target = $ship->getTraktorShip();
        if (!$target->isFleetLeader()) {
            return $msg;
        }

        $fleet = $target->getFleet();

        if ($fleet->getDefendedColony() !== null) {
            $colony = $fleet->getDefendedColony();
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
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $colony->getUser()->getId(),
                sprintf(
                    _('Die Flotte %s hat von Spieler %s die Verteidigung der Kolonie %s aufgehoben'),
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
            $colony = $fleet->getBlockedColony();
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
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $colony->getUser()->getId(),
                sprintf(
                    _('Die Flotte %s hat von Spieler %s die Blockade der Kolonie %s aufgehoben'),
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
    }
}
