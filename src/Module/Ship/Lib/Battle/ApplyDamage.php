<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\ShipInterface;

final class ApplyDamage implements ApplyDamageInterface
{

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
    }

    public function damage(
        DamageWrapper $damage_wrapper,
        ShipInterface $ship
    ): array {
        $ship->setShieldRegenerationTimer(time());
        $msg = [];
        if ($ship->getShieldState()) {
            $damage = (int)$damage_wrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_SHIELDS);
            if ($damage > $ship->getShield()) {
                $msg[] = "- Schildschaden: " . $ship->getShield();
                $msg[] = "-- Schilde brechen zusammen!";

                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $ship->setShield(0);
            } else {
                $ship->setShield($ship->getShield() - $damage);
                $msg[] = "- Schildschaden: " . $damage . " - Status: " . $ship->getShield();
            }
        }
        if ($damage_wrapper->getDamage() <= 0) {
            return $msg;
        }
        $disablemessage = false;
        $damage = (int)$damage_wrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_HULL);
        if ($ship->getCanBeDisabled() && $ship->getHuell() - $damage < round($ship->getMaxHuell() / 100 * 10)) {
            $damage = (int)round($ship->getHuell() - $ship->getMaxHuell() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $ship->setDisabled(true);
        }
        if ($ship->getHuell() > $damage) {
            $ship->setHuell($ship->getHuell() - $damage);
            $msg[] = "- Hüllenschaden: " . $damage . " - Status: " . $ship->getHuell();
            if ($disablemessage) {
                $msg[] = $disablemessage;
            }
            return $msg;
        }
        $msg[] = "- Hüllenschaden: " . $damage;
        $msg[] = "-- Das Schiff wurde zerstört!";
        $ship->setIsDestroyed(true);
        return $msg;
    }
}
