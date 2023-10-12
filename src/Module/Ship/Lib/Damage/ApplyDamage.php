<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Lib\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipSystemInterface;

//TODO unit tests and move to Lib/Damage
final class ApplyDamage implements ApplyDamageInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function damage(
        DamageWrapper $damageWrapper,
        ShipWrapperInterface $shipWrapper
    ): InformationWrapper {
        $ship = $shipWrapper->get();
        $ship->setShieldRegenerationTimer(time());

        $informations = new InformationWrapper();
        if ($ship->getShieldState()) {
            $damage = (int) $damageWrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_SHIELDS);
            if ($damage >= $ship->getShield()) {
                $informations->addInformation("- Schildschaden: " . $ship->getShield());
                $informations->addInformation("-- Schilde brechen zusammen!");

                $this->shipSystemManager->deactivate($shipWrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);

                $ship->setShield(0);
            } else {
                $ship->setShield($ship->getShield() - $damage);
                $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $ship->getShield());
            }
        }
        if ($damageWrapper->getNetDamage() <= 0) {
            return $informations;
        }
        $disablemessage = false;
        $damage = (int) $damageWrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_HULL);
        if ($ship->getSystemState(ShipSystemTypeEnum::SYSTEM_RPG_MODULE) && $ship->getHull() - $damage < round($ship->getMaxHull() / 100 * 10)) {
            $damage = (int) round($ship->getHull() - $ship->getMaxHull() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $ship->setDisabled(true);
        }
        if ($ship->getHull() > $damage) {
            if ($damageWrapper->isCrit()) {
                $systemName = $this->destroyRandomShipSystem($shipWrapper);

                if ($systemName !== null) {
                    $informations->addInformation("- Kritischer Hüllen-Treffer zerstört System: " . $systemName);
                }
            }
            $huelleVorher = $ship->getHull();
            $ship->setHuell($huelleVorher - $damage);
            $informations->addInformation("- Hüllenschaden: " . $damage . " - Status: " . $ship->getHull());

            if (!$this->checkForDamagedShipSystems(
                $shipWrapper,
                $huelleVorher,
                $informations
            )) {
                $this->damageRandomShipSystem(
                    $shipWrapper,
                    $informations,
                    (int)ceil((100 * $damage * random_int(1, 5)) / $ship->getMaxHull())
                );
            }

            if ($disablemessage) {
                $informations->addInformation($disablemessage);
            }

            if ($ship->isDestroyed()) {
                $informations->addInformation("-- Das Schiff wurde zerstört!");
            }

            return $informations;
        }
        $informations->addInformation("- Hüllenschaden: " . $damage);
        $informations->addInformation("-- Das Schiff wurde zerstört!");
        $ship->setIsDestroyed(true);

        return $informations;
    }

    public function damageBuilding(
        DamageWrapper $damageWrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $colony = $target->getColony();
        if (!$isOrbitField && $this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled()) {
            $damage = (int) $damageWrapper->getDamageRelative($colony, ShipEnum::DAMAGE_MODE_SHIELDS);
            if ($damage > $colony->getShields()) {
                $informations->addInformation("- Schildschaden: " . $colony->getShields());
                $informations->addInformation("-- Schilde brechen zusammen!");

                $colony->setShields(0);
            } else {
                $colony->setShields($colony->getShields() - $damage);
                $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $colony->getShields());
            }
        }
        if ($damageWrapper->getNetDamage() <= 0) {
            return $informations;
        }
        $damage = (int) $damageWrapper->getDamageRelative($colony, ShipEnum::DAMAGE_MODE_HULL);
        if ($target->getIntegrity() > $damage) {
            $target->setIntegrity($target->getIntegrity() - $damage);
            $informations->addInformation("- Gebäudeschaden: " . $damage . " - Status: " . $target->getIntegrity());

            return $informations;
        }
        $informations->addInformation("- Gebäudeschaden: " . $damage);
        $informations->addInformation("-- Das Gebäude wurde zerstört!");
        $target->setIntegrity(0);

        return $informations;
    }

    private function checkForDamagedShipSystems(
        ShipWrapperInterface $wrapper,
        int $huelleVorher,
        InformationWrapper $informations
    ): bool {
        $ship = $wrapper->get();
        $systemsToDamage = ceil($huelleVorher * 6 / $ship->getMaxHull()) -
            ceil($ship->getHull() * 6 / $ship->getMaxHull());

        if ($systemsToDamage == 0) {
            return false;
        }

        for ($i = 1; $i <= $systemsToDamage; $i++) {
            $this->damageRandomShipSystem($wrapper, $informations);
        }

        return true;
    }

    private function destroyRandomShipSystem(ShipWrapperInterface $wrapper): ?string
    {
        $healthySystems = $wrapper->get()->getHealthySystems();
        shuffle($healthySystems);

        if ($healthySystems === []) {
            return null;
        }
        $system = $healthySystems[0];
        $system->setStatus(0);
        $system->setMode(ShipSystemModeEnum::MODE_OFF);
        $this->shipSystemManager->handleDestroyedSystem($wrapper, $healthySystems[0]->getSystemType());

        return ShipSystemTypeEnum::getDescription($healthySystems[0]->getSystemType());
    }

    private function damageRandomShipSystem(
        ShipWrapperInterface $wrapper,
        InformationWrapper $informations,
        int $percent = null
    ): void {
        $healthySystems = $wrapper->get()->getHealthySystems();
        shuffle($healthySystems);

        if ($healthySystems !== []) {
            $system = $healthySystems[0];

            $this->damageShipSystem($wrapper, $system, $percent ?? random_int(1, 70), $informations);
        }
    }

    public function damageShipSystem(
        ShipWrapperInterface $wrapper,
        ShipSystemInterface $system,
        int $dmg,
        InformationWrapper $informations
    ): bool {
        $status = $system->getStatus();
        $systemName = ShipSystemTypeEnum::getDescription($system->getSystemType());

        if ($status > $dmg) {
            $system->setStatus($status - $dmg);
            $this->shipSystemManager->handleDamagedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Folgendes System wurde beschädigt: " . $systemName);

            return false;
        } else {
            $system->setStatus(0);
            $system->setMode(ShipSystemModeEnum::MODE_OFF);
            $this->shipSystemManager->handleDestroyedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Der Schaden zerstört folgendes System: " . $systemName);

            return true;
        }
    }
}
