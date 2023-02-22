<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WeaponInterface;

final class EnergyWeaponPhase extends AbstractWeaponPhase implements EnergyWeaponPhaseInterface
{
    public const FIRINGMODE_RANDOM = 1;
    public const FIRINGMODE_FOCUS = 2;

    public function fire(
        ?ShipWrapperInterface $wrapper,
        $attackingPhalanx,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $fightMessages = [];

        /**
         * @var ShipInterface
         */
        $attacker = $wrapper !== null ? $wrapper->get() : $attackingPhalanx;

        $targetWrapper = $targetPool[array_rand($targetPool)];

        for ($i = 1; $i <= $attacker->getRump()->getPhaserVolleys(); $i++) {
            if (count($targetPool) === 0) {
                break;
            }
            if (!$attacker->getPhaserState() || $this->hasUnsufficientEnergy($wrapper, $attackingPhalanx)) {
                break;
            }
            $this->reduceEps($wrapper, $attackingPhalanx);
            if ($this->getEnergyWeapon($attacker)->getFiringMode() === self::FIRINGMODE_RANDOM) {
                $targetWrapper = $targetPool[array_rand($targetPool)];
            }

            /**
             * @var ShipInterface
             */
            $target = $targetWrapper->get();

            $fightMessage = new FightMessage($attacker->getUser()->getId(), $target->getUser()->getId());
            $fightMessages[] = $fightMessage;

            $fightMessage->add(sprintf(
                "Die %s feuert mit einem %s auf die %s",
                $attacker->getName(),
                $this->getEnergyWeapon($attacker)->getName(),
                $target->getName()
            ));

            if (
                $attacker->getHitChance() * (100 - $target->getEvadeChance()) < rand(1, 10000)
            ) {
                $fightMessage->add("Die " . $target->getName() . " wurde verfehlt");
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

            $fightMessage->addMessageMerge($this->applyDamage->damage($damage_wrapper, $targetWrapper));

            if ($target->isDestroyed()) {
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

                $targetId = $target->getId();
                $fightMessage->add($this->shipRemover->destroy($targetWrapper));

                unset($targetPool[$targetId]);

                if ($this->getEnergyWeapon($attacker)->getFiringMode() === self::FIRINGMODE_FOCUS) {
                    break;
                }
            }
        }

        return $fightMessages;
    }

    private function hasUnsufficientEnergy(?ShipWrapperInterface $wrapper, $attackingPhalanx): bool
    {
        if ($wrapper !== null) {
            return $wrapper->getEpsSystemData()->getEps() < $this->getEnergyWeaponEnergyCosts();
        } else {
            return $attackingPhalanx->getEps() < $this->getEnergyWeaponEnergyCosts();
        }
    }

    private function reduceEps(?ShipWrapperInterface $wrapper, $attackingPhalanx): void
    {
        if ($wrapper !== null) {
            $eps = $wrapper->getEpsSystemData();
            $eps->setEps($eps->getEps() - $this->getEnergyWeaponEnergyCosts())->update();
        } else {
            $attackingPhalanx->setEps($attackingPhalanx->getEps() - $this->getEnergyWeaponEnergyCosts());
        }
    }

    public function fireAtBuilding(
        ShipWrapperInterface $attackerWrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): array {
        $msg = [];

        $attacker = $attackerWrapper->get();
        for ($i = 1; $i <= $attacker->getRump()->getPhaserVolleys(); $i++) {
            if (!$attacker->getPhaserState() || $this->hasUnsufficientEnergy($attackerWrapper, null)) {
                break;
            }
            $this->reduceEps($attackerWrapper, null);

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
            elseif ($target->hasHighDamage()) {
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
