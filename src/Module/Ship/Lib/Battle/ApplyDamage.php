<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Orm\Entity\PlanetFieldInterface;
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
            $damage = (int) $damage_wrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_SHIELDS);
            if ($damage >= $ship->getShield()) {
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
        $damage = (int) $damage_wrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_HULL);
        if ($ship->getCanBeDisabled() && $ship->getHull() - $damage < round($ship->getMaxHuell() / 100 * 10)) {
            $damage = (int) round($ship->getHull() - $ship->getMaxHuell() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $ship->setDisabled(true);
        }
        if ($ship->getHull() > $damage) {
            if ($damage_wrapper->isCrit()) {
                $systemName = $this->destroyRandomShipSystem($ship);

                if ($systemName !== null) {
                    $msg[] = "- Kritischer Hüllen-Treffer zerstört System: " . $systemName;
                }
            }
            $huelleVorher = $ship->getHull();
            $ship->setHuell($huelleVorher - $damage);
            $msg[] = "- Hüllenschaden: " . $damage . " - Status: " . $ship->getHull();

            if (!$this->checkForDamagedShipSystems($ship, $huelleVorher, $msg)) {
                $this->damageRandomShipSystem($ship, $msg, (int)ceil((100 * $damage * rand(1, 5)) / $ship->getMaxHuell()));
            }

            if ($disablemessage) {
                $msg[] = $disablemessage;
            }

            if ($ship->getIsDestroyed()) {
                $msg[] = "-- Das Schiff wurde zerstört!";
            }

            return $msg;
        }
        $msg[] = "- Hüllenschaden: " . $damage;
        $msg[] = "-- Das Schiff wurde zerstört!";
        $ship->setIsDestroyed(true);
        return $msg;
    }

    public function damageBuilding(
        DamageWrapper $damage_wrapper,
        PlanetFieldInterface $target,
        $isOrbitField
    ): array {
        $msg = [];
        $colony = $target->getColony();
        if (!$isOrbitField && $colony->getShieldState() && $colony->getShields() > 0) {
            $damage = (int) $damage_wrapper->getDamageRelative($colony, ShipEnum::DAMAGE_MODE_SHIELDS, true);
            if ($damage > $colony->getShields()) {
                $msg[] = "- Schildschaden: " . $colony->getShields();
                $msg[] = "-- Schilde brechen zusammen!";

                $colony->setShields(0);
            } else {
                $colony->setShields($colony->getShields() - $damage);
                $msg[] = "- Schildschaden: " . $damage . " - Status: " . $colony->getShields();
            }
        }
        if ($damage_wrapper->getDamage() <= 0) {
            return $msg;
        }
        $damage = (int) $damage_wrapper->getDamageRelative($colony, ShipEnum::DAMAGE_MODE_HULL, true);
        if ($target->getIntegrity() > $damage) {
            $target->setIntegrity($target->getIntegrity() - $damage);
            $msg[] = "- Gebäudeschaden: " . $damage . " - Status: " . $target->getIntegrity();

            return $msg;
        }
        $msg[] = "- Gebäudeschaden: " . $damage;
        $msg[] = "-- Das Gebäude wurde zerstört!";
        $target->setIntegrity(0);
        return $msg;
    }

    private function checkForDamagedShipSystems(ShipInterface $ship, int $huelleVorher, &$msg): bool
    {
        $systemsToDamage = ceil($huelleVorher * 6 / $ship->getMaxHuell()) -
            ceil($ship->getHull() * 6 / $ship->getMaxHuell());

        if ($systemsToDamage == 0) {
            return false;
        }

        for ($i = 1; $i <= $systemsToDamage; $i++) {
            $this->damageRandomShipSystem($ship, $msg);
        }

        return true;
    }

    private function destroyRandomShipSystem(ShipInterface $ship): ?string
    {
        $healthySystems = $ship->getHealthySystems();
        shuffle($healthySystems);

        if (empty($healthySystems)) {
            return null;
        }
        $system = $healthySystems[0];
        $system->setStatus(0);
        $system->setMode(ShipSystemModeEnum::MODE_OFF);
        $this->shipSystemManager->handleDestroyedSystem($ship, $healthySystems[0]->getSystemType());
        //catch invalidsystemexception

        return ShipSystemTypeEnum::getDescription($healthySystems[0]->getSystemType());
    }

    private function damageRandomShipSystem(ShipInterface $ship, &$msg, $percent = null): void
    {
        $healthySystems = $ship->getHealthySystems();
        shuffle($healthySystems);

        if (count($healthySystems) > 0) {
            $system = $healthySystems[0];

            $this->damageShipSystem($ship, $system, $percent ?? rand(1, 70), $msg);
            //catch invalidsystemexception
        }
    }

    public function damageShipSystem($ship, $system, $dmg, &$msg): bool
    {
        $status = $system->getStatus();
        $systemName = ShipSystemTypeEnum::getDescription($system->getSystemType());

        if ($status > $dmg) {
            $system->setStatus($status - $dmg);
            $this->shipSystemManager->handleDamagedSystem($ship, $system->getSystemType());
            $msg[] = "- Folgendes System wurde beschädigt: " . $systemName;

            return false;
        } else {
            $system->setStatus(0);
            $system->setMode(ShipSystemModeEnum::MODE_OFF);
            $this->shipSystemManager->handleDestroyedSystem($ship, $system->getSystemType());
            $msg[] = "- Der Schaden zerstört folgendes System: " . $systemName;

            return true;
        }
    }
}
