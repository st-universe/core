<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipRoleEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;

final class ProjectileWeaponPhase extends AbstractWeaponPhase implements ProjectileWeaponPhaseInterface
{

    public function fire(
        ?ShipWrapperInterface $wrapper,
        $attackingPhalanx,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $fightMessages = [];

        $attacker = $wrapper !== null ? $wrapper->get() : $attackingPhalanx;

        for ($i = 1; $i <= $attacker->getRump()->getTorpedoVolleys(); $i++) {

            if (count($targetPool) === 0) {
                break;
            }

            $targetWrapper = $targetPool[array_rand($targetPool)];
            $target = $targetWrapper->get();
            if (
                !$attacker->getTorpedoState() ||
                $this->hasUnsufficientEnergy($wrapper, $attackingPhalanx) ||
                $attacker->getTorpedoCount() === 0
            ) {
                break;
            }

            $torpedo = $attacker->getTorpedo();
            $torpedoName =  $torpedo->getName();

            if ($attacker instanceof ShipInterface) {
                $this->shipTorpedoManager->changeTorpedo($wrapper, -1);
            } else {
                $attacker->setTorpedoCount($attacker->getTorpedoCount() - 1);
            }

            $this->reduceEps($wrapper, $attackingPhalanx);

            $fightMessage = new FightMessage($attacker->getUser()->getId(), $target->getUser()->getId());
            $fightMessages[] = $fightMessage;

            $fightMessage->add("Die " . $attacker->getName() . " feuert einen " . $torpedoName . " auf die " . $target->getName());

            /* higher evade chance for pulseships against torpedo ships

            if ($attacker->getRump()->getRoleId() === ShipRoleEnum::ROLE_TORPEDOSHIP && $target->getRump()->getRoleId() === ShipRoleEnum::ROLE_PULSESHIP) {
                $hitchance = round($attacker->getHitChance() * 0.65); 
            } else { */
            $hitchance = $attacker->getHitChance();
            //}
            if ($hitchance * (100 - $target->getEvadeChance()) < rand(1, 10000)) {
                $fightMessage->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }
            $isCritical = $this->isCritical($torpedo, $target->getCloakState());
            $damage_wrapper = new DamageWrapper(
                $this->getProjectileWeaponDamage($attacker, $torpedo, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $fightMessage->addMessageMerge($this->applyDamage->damage($damage_wrapper, $targetWrapper));

            if ($target->isDestroyed()) {
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
                $this->checkForPrestige($attacker->getUser(), $target);
                $fightMessage->add($this->shipRemover->destroy($targetWrapper));
            }
        }

        return $fightMessages;
    }

    private function hasUnsufficientEnergy(?ShipWrapperInterface $wrapper, $attackingPhalanx): bool
    {
        if ($wrapper !== null) {
            return $wrapper->getEpsSystemData()->getEps() < $this->getProjectileWeaponEnergyCosts();
        } else {
            return $attackingPhalanx->getEps() < $this->getProjectileWeaponEnergyCosts();
        }
    }

    private function reduceEps(?ShipWrapperInterface $wrapper, $attackingPhalanx): void
    {
        if ($wrapper !== null) {
            $eps = $wrapper->getEpsSystemData();
            $eps->setEps($eps->getEps() - $this->getProjectileWeaponEnergyCosts())->update();
        } else {
            $attackingPhalanx->setEps($attackingPhalanx->getEps() - $this->getProjectileWeaponEnergyCosts());
        }
    }

    public function fireAtBuilding(
        ShipWrapperInterface $attackerWrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField,
        &$antiParticleCount
    ): array {
        $msg = [];

        $attacker = $attackerWrapper->get();
        for ($i = 1; $i <= $attacker->getRump()->getTorpedoVolleys(); $i++) {

            if (!$attacker->getTorpedoState() || $this->hasUnsufficientEnergy($attackerWrapper, null)) {
                break;
            }

            $torpedo = $attacker->getTorpedo();
            $this->shipTorpedoManager->changeTorpedo($attackerWrapper, -1);

            $this->reduceEps($attackerWrapper, null);

            $msg[] = sprintf(_("Die %s feuert einen %s auf das Gebäude %s auf Feld %d"), $attacker->getName(), $torpedo->getName(), $target->getBuilding()->getName(), $target->getFieldId());

            if ($antiParticleCount > 0) {
                $antiParticleCount--;
                $msg[] = "Der Torpedo wurde vom orbitalem Torpedoabwehrsystem abgefangen";
                continue;
            }
            if ($attacker->getHitChance() < rand(1, 100)) {
                $msg[] = "Das Gebäude wurde verfehlt";
                continue;
            }
            $isCritical = rand(1, 100) <= $torpedo->getCriticalChance();
            $damage_wrapper = new DamageWrapper(
                $this->getProjectileWeaponDamage($attacker, $torpedo, $isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
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

    private function isCritical($torpedo, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $torpedo->getCriticalChance() * 2 : $torpedo->getCriticalChance();
        if (rand(1, 100) <= $critChance) {
            return true;
        }
        return false;
    }

    private function getProjectileWeaponDamage($ship, $torpedo, bool $isCritical): float
    {
        $variance = (int) round($torpedo->getBaseDamage() / 100 * $torpedo->getVariance());
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(),
            false,
            $torpedo->getBaseDamage()
        );
        $damage = rand($basedamage - $variance, $basedamage + $variance);

        return $isCritical ? $damage * 2 : $damage;
    }
}
