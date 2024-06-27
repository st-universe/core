<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class TrackerDeviceManager implements TrackerDeviceManagerInterface
{
    public function __construct(
        private ShipSystemRepositoryInterface $shipSystemRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {
    }

    public function resetTrackersOfTrackedShip(
        ShipWrapperInterface $trackedShipWrapper,
        ShipSystemManagerInterface $shipSystemManager,
        bool $sendPmToTargetOwner
    ): void {

        $shipWrapperFactory = $trackedShipWrapper->getShipWrapperFactory();

        foreach ($this->shipSystemRepository->getTrackingShipSystems($trackedShipWrapper->get()->getId()) as $system) {

            $this->deactivateTrackerIfActive($shipWrapperFactory->wrapShip($system->getShip()), $sendPmToTargetOwner);
        }
    }

    public function deactivateTrackerIfActive(ShipWrapperInterface $wrapper, bool $sendPmToTargetOwner): void
    {
        $tracker = $wrapper->getTrackerSystemData();

        if ($tracker === null) {
            return;
        }

        $targetWrapper = $tracker->getTargetWrapper();
        if ($targetWrapper === null) {
            return;
        }

        $this->sendDeactivationPMs($wrapper->get(), $targetWrapper->get(), $sendPmToTargetOwner);

        $wrapper
            ->getShipSystemManager()
            ->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);
    }

    private function sendDeactivationPMs(ShipInterface $ship, ShipInterface $target, bool $sendPmToTargetOwner): void
    {
        if ($target->getUser() !== $ship->getUser()) {

            //send pm to target owner
            if ($sendPmToTargetOwner) {
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $target->getUser()->getId(),
                    sprintf(
                        'Die Crew der %s hat einen Transponder gefunden und deaktiviert. %s',
                        $target->getName(),
                        $this->getTrackerSource($ship->getUser())
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }

            //send pm to tracker owner
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $ship->getUser()->getId(),
                sprintf(
                    'Die %s hat die Verbindung zum Tracker verloren',
                    $ship->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
        }
    }

    private function getTrackerSource(UserInterface $user): string
    {
        switch (random_int(0, 2)) {
            case 0:
                return _('Der Ursprung kann nicht identifiziert werden');
            case 1:
                return sprintf(_('Der Ursprung lässt auf %s schließen'), $user->getName());
            case 2:
                return sprintf(_('Der Ursprung lässt darauf schließen, dass er %s-Herkunft ist'), $user->getFaction()->getName());
            default:
                return '';
        }
    }
}
