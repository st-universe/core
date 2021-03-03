<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

final class EnergyWeaponPhase implements EnergyWeaponPhaseInterface
{

    public const FIRINGMODE_RANDOM = 1;
    public const FIRINGMODE_FOCUS = 2;

    private WeaponRepositoryInterface $weaponRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ApplyDamageInterface $applyDamage;

    private ModuleValueCalculatorInterface $moduleValueCalculator;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        WeaponRepositoryInterface $weaponRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ApplyDamageInterface $applyDamage,
        ModuleValueCalculatorInterface $moduleValueCalculator,
        BuildingManagerInterface $buildingManager
    ) {
        $this->weaponRepository = $weaponRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->applyDamage = $applyDamage;
        $this->moduleValueCalculator = $moduleValueCalculator;
        $this->buildingManager = $buildingManager;
    }

    public function fire(
        $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $msg = [];

        $target = $targetPool[array_rand($targetPool)];

        for ($i = 1; $i <= $attacker->getRump()->getPhaserVolleys(); $i++) {
            if (!$attacker->getPhaser() || $attacker->getEps() < $this->getEnergyWeaponEnergyCosts()) {
                break;
            }
            $attacker->setEps($attacker->getEps() - $this->getEnergyWeaponEnergyCosts());
            if ($this->getEnergyWeapon($attacker)->getFiringMode() === self::FIRINGMODE_RANDOM) {
                if (count($targetPool) === 0) {
                    break;
                }

                $target = $targetPool[array_rand($targetPool)];
            }

            $msg[] = "Die " . $attacker->getName() . " feuert mit einem " . $this->getEnergyWeapon($attacker)->getName() . " auf die " . $target->getName();

            if (
                $attacker->getHitChance() * (100 - $target->getEvadeChance()) < rand(1, 10000)
            ) {
                $msg[] = "Die " . $target->getName() . " wurde verfehlt";
                continue;
            }
            $isCritical = $this->isCritical($attacker, $target->getCloakState());
            $damage_wrapper = new DamageWrapper(
                $this->getEnergyWeaponDamage($attacker, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getRump()->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getRump()->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damage($damage_wrapper, $target));

            if ($target->getIsDestroyed()) {
                if ($isAlertRed) {
                    $this->entryCreator->addShipEntry(
                        '[b][color=red]Alarm-Rot:[/color][/b] Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                        $attacker->getUser()->getId()
                    );
                } else {
                    $this->entryCreator->addShipEntry(
                        'Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                        $attacker->getUser()->getId()
                    );
                }

                $targetId = $target->getId();
                $destroyMsg = $this->shipRemover->destroy($target);
                if ($destroyMsg !== null) {
                    $msg[] = $destroyMsg;
                }

                unset($targetPool[$targetId]);

                if ($this->getEnergyWeapon($attacker)->getFiringMode() === self::FIRINGMODE_FOCUS) {
                    break;
                }
            }
        }

        return $msg;
    }

    public function fireAtBuilding(
        ShipInterface $attacker,
        PlanetFieldInterface $target,
        $isOrbitField
    ): array {
        $msg = [];

        for ($i = 1; $i <= $attacker->getRump()->getPhaserVolleys(); $i++) {
            if (!$attacker->getPhaser() || $attacker->getEps() < $this->getEnergyWeaponEnergyCosts()) {
                break;
            }
            $attacker->setEps($attacker->getEps() - $this->getEnergyWeaponEnergyCosts());

            $msg[] = sprintf(_("Die %s feuert mit einem %s auf das Gebäude %s auf Feld %d"), $attacker->getName(), $this->getEnergyWeapon($attacker)->getName(), $target->getBuilding()->getName(), $target->getFieldId());

            if (
                $attacker->getHitChance() < rand(1, 100)
            ) {
                $msg[] = _("Das Gebäude wurde verfehlt");
                continue;
            }
            $isCritical = rand(1, 100) <= $this->getEnergyWeapon($attacker)->getCriticalChance();
            $damage_wrapper = new DamageWrapper(
                $this->getEnergyWeaponDamage($attacker, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getRump()->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getRump()->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {

                $this->buildingManager->remove($target);

                break;
            }
            //deactivate if high damage
            else if ($target->hasHighDamage()) {

                $this->buildingManager->deactivate($target);
            }
        }

        return $msg;
    }

    private function isCritical($ship, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $this->getEnergyWeapon($ship)->getCriticalChance() * 2 : $this->getEnergyWeapon($ship)->getCriticalChance();
        if (rand(1, 100) <= $critChance) {
            return true;
        }
        return false;
    }

    private function getEnergyWeaponDamage($ship, bool $isCritical): float
    {
        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            return 0;
        }
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModule(),
            'getBaseDamage'
        );
        $variance = (int) round($basedamage / 100 * $this->getEnergyWeapon($ship)->getVariance());
        $damage = rand($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }

    private function getEnergyWeapon($ship): ?WeaponInterface
    {
        return $this->weaponRepository->findByModule(
            (int) $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModuleId()
        );
    }

    public function getEnergyWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }
}
