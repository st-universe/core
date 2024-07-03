<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Override;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipSystemInterface;

//TODO unit tests and move to Lib/Damage
final class ApplyDamage implements ApplyDamageInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ShipSystemManagerInterface $shipSystemManager
    ) {
    }

    #[Override]
    public function damage(
        DamageWrapper $damageWrapper,
        ShipWrapperInterface $shipWrapper,
        InformationInterface $informations
    ): void {

        if ($damageWrapper->getNetDamage() <= 0) {
            throw new RuntimeException('this should not happen');
        }

        $ship = $shipWrapper->get();

        if ($ship->getShieldState()) {

            if ($damageWrapper->isShieldPenetration()) {
                $informations->addInformationf('- Projektil hat Schilde durchdrungen!');
            } else {
                $this->damageShields($shipWrapper, $damageWrapper, $informations);
            }
        }
        if ($damageWrapper->getNetDamage() <= 0) {
            return;
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

            return;
        }
        $informations->addInformation("- Hüllenschaden: " . $damage);
        $informations->addInformation("-- Das Schiff wurde zerstört!");
        $ship->setIsDestroyed(true);
    }

    private function damageShields(ShipWrapperInterface $wrapper, DamageWrapper $damageWrapper, InformationInterface $informations): void
    {
        $ship = $wrapper->get();

        $damage = (int) $damageWrapper->getDamageRelative($ship, ShipEnum::DAMAGE_MODE_SHIELDS);
        if ($damage >= $ship->getShield()) {
            $informations->addInformation("- Schildschaden: " . $ship->getShield());
            $informations->addInformation("-- Schilde brechen zusammen!");

            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS);

            $ship->setShield(0);
        } else {
            $ship->setShield($ship->getShield() - $damage);
            $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $ship->getShield());
        }

        $ship->setShieldRegenerationTimer(time());
    }

    #[Override]
    public function damageBuilding(
        DamageWrapper $damageWrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $colony = $target->getHost();
        if (!$colony instanceof ColonyInterface) {
            throw new RuntimeException('this should not happen');
        }

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
        InformationInterface $informations
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

        return $healthySystems[0]->getSystemType()->getDescription();
    }

    private function damageRandomShipSystem(
        ShipWrapperInterface $wrapper,
        InformationInterface $informations,
        int $percent = null
    ): void {
        $healthySystems = $wrapper->get()->getHealthySystems();
        shuffle($healthySystems);

        if ($healthySystems !== []) {
            $system = $healthySystems[0];

            $this->damageShipSystem($wrapper, $system, $percent ?? random_int(1, 70), $informations);
        }
    }

    #[Override]
    public function damageShipSystem(
        ShipWrapperInterface $wrapper,
        ShipSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool {
        $status = $system->getStatus();
        $systemName = $system->getSystemType()->getDescription();

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
