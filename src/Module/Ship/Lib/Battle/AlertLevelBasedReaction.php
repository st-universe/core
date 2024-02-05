<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class AlertLevelBasedReaction implements AlertLevelBasedReactionInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
    }

    public function react(ShipWrapperInterface $wrapper): InformationWrapper
    {
        $ship = $wrapper->get();
        $informations = new InformationWrapper();

        if ($this->changeFromGreenToYellow($wrapper, $informations)) {
            return $informations;
        }

        if ($ship->getAlertState() === ShipAlertStateEnum::ALERT_YELLOW && $this->doAlertYellowReactions($wrapper, $informations)) {
            return $informations;
        }

        if ($ship->getAlertState() === ShipAlertStateEnum::ALERT_RED) {
            if ($this->doAlertYellowReactions($wrapper, $informations)) {
                return $informations;
            }
            $this->doAlertRedReactions($wrapper, $informations);
        }

        return $informations;
    }

    private function changeFromGreenToYellow(ShipWrapperInterface $wrapper, InformationWrapper $informations): bool
    {
        $ship = $wrapper->get();

        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            try {
                $alertMsg = $wrapper->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
                $informations->addInformation("- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb");
                if ($alertMsg !== null) {
                    $informations->addInformation("- " . $alertMsg);
                }
                return true;
            } catch (InsufficientEnergyException $e) {
                $informations->addInformation("- Nicht genügend Energie vorhanden um auf Alarm-Gelb zu wechseln");
                return true;
            }
        }

        return false;
    }

    private function doAlertYellowReactions(ShipWrapperInterface $wrapper, InformationWrapper $informations): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
                $informations->addInformation("- Die Tarnung wurde deaktiviert");
            } catch (ShipSystemException $e) {
            }

            return true;
        }

        if (!$ship->isTractoring() && !$ship->isTractored()) {
            try {
                $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $informations->addInformation("- Die Schilde wurden aktiviert");
            } catch (ShipSystemException $e) {
            }
        } else {
            $informations->addInformation("- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden");
        }
        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_NBS);

            $informations->addInformation("- Die Nahbereichssensoren wurden aktiviert");
        } catch (ShipSystemException $e) {
        }

        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_PHASER);

            $informations->addInformation("- Die Energiewaffe wurde aktiviert");
        } catch (ShipSystemException $e) {
        }

        return false;
    }

    private function doAlertRedReactions(ShipWrapperInterface $wrapper, InformationWrapper $informations): void
    {
        try {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO);

            $informations->addInformation("- Der Torpedowerfer wurde aktiviert");
        } catch (ShipSystemException $e) {
        }
    }
}
