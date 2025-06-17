<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageModeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

//TODO unit tests and move to Lib/Damage
final class ApplyDamage implements ApplyDamageInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    public function damage(
        DamageWrapper $damageWrapper,
        SpacecraftWrapperInterface $shipWrapper,
        InformationInterface $informations
    ): void {

        if ($damageWrapper->getNetDamage() <= 0) {
            throw new RuntimeException('this should not happen');
        }

        $ship = $shipWrapper->get();

        if ($ship->isShielded()) {

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
        $damage = (int) $damageWrapper->getDamageRelative($ship, DamageModeEnum::HULL);
        if ($ship->getSystemState(SpacecraftSystemTypeEnum::RPG_MODULE) && $ship->getHull() - $damage < round($ship->getMaxHull() / 100 * 10)) {
            $damage = (int) round($ship->getHull() - $ship->getMaxHull() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $ship->setDisabled(true);
        }
        if ($ship->getHull() > $damage) {
            if ($damageWrapper->isCrit()) {
                $systemName = $this->destroyRandomShipSystem($shipWrapper, $damageWrapper);

                if ($systemName !== null) {
                    $informations->addInformation("- Kritischer Hüllen-Treffer zerstört System: " . $systemName);
                }
            }
            $huelleVorher = $ship->getHull();
            $ship->setHuell($huelleVorher - $damage);
            $informations->addInformation("- Hüllenschaden: " . $damage . " - Status: " . $ship->getHull());

            if (!$this->checkForDamagedShipSystems(
                $shipWrapper,
                $damageWrapper,
                $huelleVorher,
                $informations
            )) {
                $this->damageRandomShipSystem(
                    $shipWrapper,
                    $damageWrapper,
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

    private function damageShields(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper, InformationInterface $informations): void
    {
        $ship = $wrapper->get();

        $damage = (int) $damageWrapper->getDamageRelative($ship, DamageModeEnum::SHIELDS);
        if ($damage >= $ship->getShield()) {
            $informations->addInformation("- Schildschaden: " . $ship->getShield());
            $informations->addInformation("-- Schilde brechen zusammen!");

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS);

            $ship->setShield(0);
        } else {
            $ship->setShield($ship->getShield() - $damage);
            $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $ship->getShield());
        }

        $shieldSystemData = $wrapper->getShieldSystemData();
        if ($shieldSystemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $shieldSystemData->setShieldRegenerationTimer(time())->update();
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
            $damage = (int) $damageWrapper->getDamageRelative($colony, DamageModeEnum::SHIELDS);
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
        $damage = (int) $damageWrapper->getDamageRelative($colony, DamageModeEnum::HULL);
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
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
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
            $this->damageRandomShipSystem($wrapper, $damageWrapper, $informations);
        }

        return true;
    }

    private function destroyRandomShipSystem(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper): ?string
    {
        $healthySystems = $this->getHealthySystems($wrapper, $damageWrapper);
        shuffle($healthySystems);

        if ($healthySystems === []) {
            return null;
        }
        $system = $healthySystems[0];
        $system->setStatus(0);
        $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $this->spacecraftSystemManager->handleDestroyedSystem($wrapper, $healthySystems[0]->getSystemType());

        return $healthySystems[0]->getSystemType()->getDescription();
    }

    private function damageRandomShipSystem(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
        InformationInterface $informations,
        ?int $percent = null
    ): void {
        $healthySystems = $this->getHealthySystems($wrapper, $damageWrapper);
        shuffle($healthySystems);

        if ($healthySystems !== []) {
            $system = $healthySystems[0];

            $this->damageShipSystem($wrapper, $system, $percent ?? random_int(1, 70), $informations);
        }
    }

    /** @return array<SpacecraftSystemInterface>  */
    private function getHealthySystems(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper): array
    {
        return $wrapper->get()->getSystems()
            ->filter(fn(SpacecraftSystemInterface $system): bool => $damageWrapper->canDamageSystem($system->getSystemType()))
            ->filter(fn(SpacecraftSystemInterface $system): bool => $system->getStatus() > 0)
            ->filter(fn(SpacecraftSystemInterface $system): bool => $system->getSystemType()->canBeDamaged())
            ->toArray();
    }

    #[Override]
    public function damageShipSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool {
        $status = $system->getStatus();
        $systemName = $system->getSystemType()->getDescription();

        if ($status > $dmg) {
            $system->setStatus($status - $dmg);
            $this->spacecraftSystemManager->handleDamagedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Folgendes System wurde beschädigt: " . $systemName);

            return false;
        } else {
            $system->setStatus(0);
            $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $this->spacecraftSystemManager->handleDestroyedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Der Schaden zerstört folgendes System: " . $systemName);

            return true;
        }
    }
}
