<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
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
            if ($damage_wrapper->isCrit())
            {
                $systemName = $this->destroyRandomShipSystem($ship);
                $msg[] = "- Kritischer Hüllen-Treffer zerstört System: " . $systemName;
            }
            $huelleVorher = $ship->getHuell();
            $ship->setHuell($huelleVorher - $damage);
            $msg[] = "- Hüllenschaden: " . $damage . " - Status: " . $ship->getHuell();

            if (!$this->checkForDestroyedShipSystems($ship, $huelleVorher, $msg))
            {
                $this->damageRandomShipSystem($ship, $msg);
            }

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
    
    private function checkForDestroyedShipSystems(ShipInterface $ship, int $huelleVorher, &$msg): bool
    {
        $systemsToDestroy = ceil($huelleVorher * 6 / $ship->getMaxHuell()) -
        ceil($ship->getHuell() * 6 / $ship->getMaxHuell());

        if ($systemsToDestroy == 0)
        {
            return false;
        }
        
        for ($i = 1; $i <= $systemsToDestroy; $i++) {
            $systemName = $this->destroyRandomShipSystem($ship);
            $msg[] = "- Der Schaden zerstört folgendes System: " . $systemName;
        }
        
        return true;
    }

    private function destroyRandomShipSystem(ShipInterface $ship): string
    {
        $healthySystems = $ship->getHealthySystems();
        shuffle($healthySystems);
        
        $healthySystems[0]->setStatus(0);
        $healthySystems[0]->setMode(ShipSystemModeEnum::MODE_OFF);
        
        return ShipSystemTypeEnum::getDescription($healthySystems[0]->getSystemType());
    }
    
    private function damageRandomShipSystem(ShipInterface $ship, &$msg): void
    {
        $healthySystems = $ship->getHealthySystems();
        shuffle($healthySystems);
        
        $system = $healthySystems[0];
        $status = $system->getStatus();
        $dmg = rand(1, 70);
        
        $systemName = ShipSystemTypeEnum::getDescription($system->getSystemType());

        if ($status > $dmg)
        {
            $system->setStatus($status - $dmg);
            $this->shipSystemManager->handleDamagedSystem($ship, $system->getSystemType());
            $msg[] = "- Folgendes System wurde beschädigt: " . $systemName;
        } else {
            $system->setStatus(0);
            $system->setMode(ShipSystemModeEnum::MODE_OFF);
            $this->shipSystemManager->handleDestroyedSystem($ship, $system->getSystemType());
            $msg[] = "- Der Schaden zerstört folgendes System: " . $systemName;
        }
    }
}
