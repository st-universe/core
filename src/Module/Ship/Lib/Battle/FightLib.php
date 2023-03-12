<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class FightLib implements FightLibInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private CancelRepairInterface $cancelRepair;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        CancelRepairInterface $cancelRepair
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelRepair = $cancelRepair;
    }

    public function ready(ShipWrapperInterface $wrapper): array
    {
        $ship = $wrapper->get();
        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        } catch (ShipSystemException $e) {
        }
        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
        } catch (ShipSystemException $e) {
        }

        $this->cancelRepair->cancelRepair($ship);

        $msg = $this->alertLevelBasedReaction($wrapper);

        if ($msg !== []) {
            $msg = array_merge([sprintf(_('Aktionen der %s'), $ship->getName())], $msg);
        }

        return $msg;
    }

    private function alertLevelBasedReaction(ShipWrapperInterface $wrapper): array
    {
        $ship = $wrapper->get();
        $msg = [];
        if ($ship->getRump()->isEscapePods() || $ship->isDestroyed()) {
            return $msg;
        }
        if ($ship->getBuildplan() === null) {
            return $msg;
        }
        if (!$ship->hasEnoughCrew() || $ship->getRump()->isTrumfield()) {
            return $msg;
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
            $msg[] = "- Das Schiff hat abgedockt";
        }
        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            try {
                $alertMsg = null;
                $wrapper->setAlertState(ShipAlertStateEnum::ALERT_YELLOW, $alertMsg);
                $msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb";
                if ($alertMsg !== null) {
                    $msg[] = "- " . $alertMsg;
                }
                return $msg;
            } catch (ShipSystemException $e) {
                $msg[] = "- Nicht genügend Energie vorhanden um auf Alarm-Gelb zu wechseln";
                return $msg;
            }
        }
        if ($ship->getCloakState() && $ship->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
                $msg[] = "- Die Tarnung wurde deaktiviert";
                return $msg;
            } catch (ShipSystemException $e) {
            }
        }
        if ($ship->getTractoredShip() === null && $ship->getTractoringShip() === null) {
            try {
                $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $msg[] = "- Die Schilde wurden aktiviert";
            } catch (ShipSystemException $e) {
            }
        } else {
            $msg[] = "- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden";
        }
        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_NBS);

            $msg[] = "- Die Nahbereichssensoren wurden aktiviert";
        } catch (ShipSystemException $e) {
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_PHASER);

                $msg[] = "- Die Energiewaffe wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_RED) {
            try {
                $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO);

                $msg[] = "- Der Torpedowerfer wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        return $msg;
    }

    public function filterInactiveShips(array $base): array
    {
        return array_filter(
            $base,
            function (ShipWrapperInterface $wrapper): bool {
                return !$wrapper->get()->isDestroyed() && !$wrapper->get()->getDisabled();
            }
        );
    }
}
