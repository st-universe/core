<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class TrackerDeviceManager implements TrackerDeviceManagerInterface
{
    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function resetTrackersOfTrackedShip(
        SpacecraftWrapperInterface $trackedShipWrapper,
        SpacecraftSystemManagerInterface $spacecraftSystemManager,
        bool $sendPmToTargetOwner
    ): void {

        $spacecraftWrapperFactory = $trackedShipWrapper->getSpacecraftWrapperFactory();

        foreach ($this->shipSystemRepository->getTrackingShipSystems($trackedShipWrapper->get()->getId()) as $system) {

            $this->deactivateTrackerIfActive($spacecraftWrapperFactory->wrapSpacecraft($system->getSpacecraft()), $sendPmToTargetOwner);
        }
    }

    #[Override]
    public function deactivateTrackerIfActive(SpacecraftWrapperInterface $wrapper, bool $sendPmToTargetOwner): void
    {
        $tracker = $wrapper instanceof ShipWrapperInterface ? $wrapper->getTrackerSystemData() : null;
        if ($tracker === null) {
            return;
        }

        $targetWrapper = $tracker->getTargetWrapper();
        if ($targetWrapper === null) {
            return;
        }

        $this->sendDeactivationPMs($wrapper->get(), $targetWrapper->get(), $sendPmToTargetOwner);

        $wrapper
            ->getSpacecraftSystemManager()
            ->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACKER, true);
    }

    private function sendDeactivationPMs(Spacecraft $spacecraft, Ship $target, bool $sendPmToTargetOwner): void
    {
        if ($target->getUser() !== $spacecraft->getUser()) {

            //send pm to target owner
            if ($sendPmToTargetOwner) {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $target->getUser()->getId(),
                    sprintf(
                        'Die Crew der %s hat einen Transponder gefunden und deaktiviert. %s',
                        $target->getName(),
                        $this->getTrackerSource($spacecraft->getUser())
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }

            //send pm to tracker owner
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $spacecraft->getUser()->getId(),
                sprintf(
                    'Die %s hat die Verbindung zum Tracker verloren',
                    $spacecraft->getName()
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
        }
    }

    private function getTrackerSource(User $user): string
    {
        return match (random_int(0, 2)) {
            0 => _('Der Ursprung kann nicht identifiziert werden'),
            1 => sprintf(_('Der Ursprung lässt auf %s schließen'), $user->getName()),
            2 => sprintf(_('Der Ursprung lässt darauf schließen, dass er %s-Herkunft ist'), $user->getFaction()->getName()),
        };
    }
}
