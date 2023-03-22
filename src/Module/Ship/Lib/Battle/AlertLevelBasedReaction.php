<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class AlertLevelBasedReaction implements AlertLevelBasedReactionInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
    }

    public function react(ShipWrapperInterface $wrapper): array
    {
        $ship = $wrapper->get();
        $msg = [];

        if ($this->changeFromGreenToYellow($wrapper, $msg)) {
            return $msg;
        }

        if ($ship->getAlertState() === ShipAlertStateEnum::ALERT_YELLOW) {
            if ($this->doAlertYellowReactions($wrapper, $msg)) {
                return $msg;
            }
        }

        if ($ship->getAlertState() === ShipAlertStateEnum::ALERT_RED) {
            if ($this->doAlertYellowReactions($wrapper, $msg)) {
                return $msg;
            }
            $this->doAlertRedReactions($wrapper, $msg);
        }

        return $msg;
    }

    /**
     * @param array<string> $msg
     */
    private function changeFromGreenToYellow(ShipWrapperInterface $wrapper, array &$msg): bool
    {
        $ship = $wrapper->get();

        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            try {
                $alertMsg = $wrapper->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
                $msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb";
                if ($alertMsg !== null) {
                    $msg[] = "- " . $alertMsg;
                }
                return true;
            } catch (InsufficientEnergyException $e) {
                $msg[] = "- Nicht genügend Energie vorhanden um auf Alarm-Gelb zu wechseln";
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string> $msg
     */
    private function doAlertYellowReactions(ShipWrapperInterface $wrapper, array &$msg): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
                $msg[] = "- Die Tarnung wurde deaktiviert";
            } catch (ShipSystemException $e) {
            }

            return true;
        }

        if (!$ship->isTractoring() && !$ship->isTractored()) {
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

        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_PHASER);

            $msg[] = "- Die Energiewaffe wurde aktiviert";
        } catch (ShipSystemException $e) {
        }

        return false;
    }

    /**
     * @param array<string> $msg
     */
    private function doAlertRedReactions(ShipWrapperInterface $wrapper, array &$msg): void
    {
        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO);

            $msg[] = "- Der Torpedowerfer wurde aktiviert";
        } catch (ShipSystemException $e) {
        }
    }
}
