<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class AlertLevelBasedReaction implements AlertLevelBasedReactionInterface
{
    public function __construct(private SpacecraftSystemManagerInterface $spacecraftSystemManager) {}

    #[Override]
    public function react(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void
    {
        $ship = $wrapper->get();

        if ($this->changeFromGreenToYellow($wrapper, $informations)) {
            return;
        }

        if ($ship->getAlertState() === SpacecraftAlertStateEnum::ALERT_YELLOW && $this->doAlertYellowReactions($wrapper, $informations)) {
            return;
        }

        if ($ship->getAlertState() === SpacecraftAlertStateEnum::ALERT_RED) {
            if ($this->doAlertYellowReactions($wrapper, $informations)) {
                return;
            }
            $this->doAlertRedReactions($wrapper, $informations);
        }
    }

    private function changeFromGreenToYellow(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): bool
    {
        $ship = $wrapper->get();

        if ($ship->getAlertState() == SpacecraftAlertStateEnum::ALERT_GREEN) {
            try {
                $alertMsg = $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_YELLOW);
                $informations->addInformation("- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb");
                if ($alertMsg !== null) {
                    $informations->addInformation("- " . $alertMsg);
                }
                return true;
            } catch (InsufficientEnergyException) {
                $informations->addInformation("- Nicht genügend Energie vorhanden um auf Alarm-Gelb zu wechseln");
                return true;
            }
        }

        return false;
    }

    private function doAlertYellowReactions(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            try {
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SYSTEM_CLOAK);
                $informations->addInformation("- Die Tarnung wurde deaktiviert");
            } catch (SpacecraftSystemException) {
            }

            return true;
        }

        if (!$ship->isTractoring() && (!$ship instanceof ShipInterface || !$ship->isTractored())) {
            try {
                $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::SYSTEM_SHIELDS);

                $informations->addInformation("- Die Schilde wurden aktiviert");
            } catch (SpacecraftSystemException) {
            }
        } else {
            $informations->addInformation("- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden");
        }
        try {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::SYSTEM_NBS);

            $informations->addInformation("- Die Nahbereichssensoren wurden aktiviert");
        } catch (SpacecraftSystemException) {
        }

        try {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::SYSTEM_PHASER);

            $informations->addInformation("- Die Energiewaffe wurde aktiviert");
        } catch (SpacecraftSystemException) {
        }

        return false;
    }

    private function doAlertRedReactions(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void
    {
        try {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::SYSTEM_TORPEDO);

            $informations->addInformation("- Der Torpedowerfer wurde aktiviert");
        } catch (SpacecraftSystemException) {
        }
    }
}
