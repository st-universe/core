<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\ShipInterface;

final class FightLib implements FightLibInterface
{

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
    }

    public function ready(ShipInterface $ship): array
    {
        try {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);
        } catch (ShipSystemException $e) {
        }

        $ship->cancelRepair();

        $msg = $this->alertLevelBasedReaction($ship);

        if ($msg !== []) {
            $msg = array_merge([sprintf(_('Aktionen der %s'), $ship->getName())], $msg);
        }

        return $msg;
    }

    private function alertLevelBasedReaction(ShipInterface $ship): array
    {
        $msg = [];
        if ($ship->getRump()->isEscapePods() || $ship->getIsDestroyed()) {
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
                $ship->setAlertState(ShipAlertStateEnum::ALERT_YELLOW, $alertMsg);
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
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);
                $msg[] = "- Die Tarnung wurde deaktiviert";
                return $msg;
            } catch (ShipSystemException $e) {
            }
        }
        if (!$ship->isTraktorbeamActive()) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $msg[] = "- Die Schilde wurden aktiviert";
            } catch (ShipSystemException $e) {
            }
        } else {
            $msg[] = "- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden";
        }
        try {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_NBS);

            $msg[] = "- Die Nahbereichssensoren wurden aktiviert";
        } catch (ShipSystemException $e) {
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_PHASER);

                $msg[] = "- Die Energiewaffe wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_RED) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_TORPEDO);

                $msg[] = "- Der Torpedowerfer wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        return $msg;
    }

    /**
     * @return ShipInterface[]
     */
    public function filterInactiveShips(array $base): array
    {
        return array_filter(
            $base,
            function (ShipInterface $ship): bool {
                return !$ship->getIsDestroyed() && !$ship->getDisabled();
            }
        );
    }
}
