<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Ship\ShipRoleEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;

final class ProjectileWeaponPhase implements ProjectileWeaponPhaseInterface
{

    private ShipSystemManagerInterface $shipSystemManager;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ApplyDamageInterface $applyDamage;

    private ModuleValueCalculatorInterface $moduleValueCalculator;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ApplyDamageInterface $applyDamage,
        ModuleValueCalculatorInterface $moduleValueCalculator,
        BuildingManagerInterface $buildingManager
    ) {
        $this->shipSystemManager = $shipSystemManager;
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

        for ($i = 1; $i <= $attacker->getRump()->getTorpedoVolleys(); $i++) {
            if (count($targetPool) === 0) {
                break;
            }
            $target = $targetPool[array_rand($targetPool)];
            if (
                !$attacker->getTorpedos() ||
                $attacker->getEps() < $this->getProjectileWeaponEnergyCosts() ||
                $attacker->getTorpedoCount() === 0
            ) {
                break;
            }
            $attacker->setTorpedoCount($attacker->getTorpedoCount() - 1);

            if ($attacker->getTorpedoCount() === 0) {
                if ($attacker instanceof ShipInterface) {
                    $this->shipSystemManager->deactivate($attacker, ShipSystemTypeEnum::SYSTEM_TORPEDO, true);
                }
            }

            $attacker->setEps($attacker->getEps() - $this->getProjectileWeaponEnergyCosts());

            $msg[] = "Die " . $attacker->getName() . " feuert einen " . $attacker->getTorpedo()->getName() . " auf die " . $target->getName();

            // higher evade chance for pulseships against torpedo ships

            if ($attacker->getRump()->getRoleId() === ShipRoleEnum::ROLE_TORPEDOSHIP && $target->getRump()->getRoleId() === ShipRoleEnum::ROLE_PULSESHIP) {
                $hitchance = round($attacker->getHitChance() * 0.65);
            } else {
                $hitchance = $attacker->getHitChance();
            }
            if ($hitchance * (100 - $target->getEvadeChance()) < rand(1, 10000)) {
                $msg[] = "Die " . $target->getName() . " wurde verfehlt";
                continue;
            }
            $isCritical = $this->isCritical($attacker, $target->getCloakState());
            $damage_wrapper = new DamageWrapper(
                $this->getProjectileWeaponDamage($attacker, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getTorpedo()->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getTorpedo()->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damage($damage_wrapper, $target));

            if ($target->getIsDestroyed()) {
                unset($targetPool[$target->getId()]);

                if ($isAlertRed) {
                    $this->entryCreator->addShipEntry(
                        '[b][color=red]Alarm-Rot:[/color][/b] Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                        $attacker->getUser()->getId()
                    );
                } else {
                    $entryMsg = sprintf(
                        'Die %s (%s) wurde in Sektor %s von der %s zerstört',
                        $target->getName(),
                        $target->getRump()->getName(),
                        $target->getSectorString(),
                        $attacker->getName()
                    );
                    if ($target->isBase()) {
                        $this->entryCreator->addStationEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    } else {
                        $this->entryCreator->addShipEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    }
                }
                $destroyMsg = $this->shipRemover->destroy($target);
                if ($destroyMsg !== null) {
                    $msg[] = $destroyMsg;
                }
            }
        }

        return $msg;
    }

    public function fireAtBuilding(
        ShipInterface $attacker,
        PlanetFieldInterface $target,
        $isOrbitField,
        &$antiParticleCount
    ): array {
        $msg = [];

        for ($i = 1; $i <= $attacker->getRump()->getTorpedoVolleys(); $i++) {
            if (!$attacker->getTorpedos() || $attacker->getEps() < $this->getProjectileWeaponEnergyCosts()) {
                break;
            }
            $attacker->setTorpedoCount($attacker->getTorpedoCount() - 1);

            if ($attacker->getTorpedoCount() === 0) {
                $this->shipSystemManager->deactivate($attacker, ShipSystemTypeEnum::SYSTEM_TORPEDO, true);
            }

            $attacker->setEps($attacker->getEps() - $this->getProjectileWeaponEnergyCosts());

            $msg[] = sprintf(_("Die %s feuert einen %s auf das Gebäude %s auf Feld %d"), $attacker->getName(), $attacker->getTorpedo()->getName(), $target->getBuilding()->getName(), $target->getFieldId());

            if ($antiParticleCount > 0) {
                $antiParticleCount--;
                $msg[] = "Der Torpedo wurde vom orbitalem Torpedoabwehrsystem abgefangen";
                continue;
            }
            if ($attacker->getHitChance() < rand(1, 100)) {
                $msg[] = "Das Gebäude wurde verfehlt";
                continue;
            }
            $isCritical = rand(1, 100) <= $attacker->getTorpedo()->getCriticalChance();
            $damage_wrapper = new DamageWrapper(
                $this->getProjectileWeaponDamage($attacker, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($attacker->getTorpedo()->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getTorpedo()->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {


                $this->entryCreator->addColonyEntry(
                    sprintf(
                        _('Das Gebäude %s auf Kolonie %s wurde von der %s zerstört'),
                        $target->getBuilding()->getName(),
                        $target->getColony()->getName(),
                        $attacker->getName()
                    )
                );

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

    private function getProjectileWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    private function isCritical($ship, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $ship->getTorpedo()->getCriticalChance() * 2 : $ship->getTorpedo()->getCriticalChance();
        if (rand(1, 100) <= $critChance) {
            return true;
        }
        return false;
    }

    private function getProjectileWeaponDamage($ship, bool $isCritical): float
    {
        $variance = (int) round($ship->getTorpedo()->getBaseDamage() / 100 * $ship->getTorpedo()->getVariance());
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(),
            false,
            $ship->getTorpedo()->getBaseDamage()
        );
        $damage = rand($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }
}
