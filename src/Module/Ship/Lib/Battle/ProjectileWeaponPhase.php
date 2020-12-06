<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\ShipRoleEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;

final class ProjectileWeaponPhase implements ProjectileWeaponPhaseInterface
{

    private ShipSystemManagerInterface $shipSystemManager;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ApplyDamageInterface $applyDamage;

    private ModuleValueCalculatorInterface $moduleValueCalculator;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ApplyDamageInterface $applyDamage,
        ModuleValueCalculatorInterface $moduleValueCalculator
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->applyDamage = $applyDamage;
        $this->moduleValueCalculator = $moduleValueCalculator;
    }

    public function fire(
        ShipInterface $attacker,
        array $targetPool
    ): array {
        $msg = [];

        for ($i = 1; $i <= $attacker->getRump()->getTorpedoVolleys(); $i++) {
            if ($targetPool === []) {
                break;
            }
            $target = $targetPool[array_rand($targetPool)];
            if (!$attacker->getTorpedos() || $attacker->getEps() < $this->getProjectileWeaponEnergyCosts()) {
                break;
            }
            $attacker->setTorpedoCount($attacker->getTorpedoCount() - 1);

            if ($attacker->getTorpedoCount() === 0) {
                $this->shipSystemManager->deactivate($attacker, ShipSystemTypeEnum::SYSTEM_TORPEDO);
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
            $damage_wrapper = new DamageWrapper(
                $this->getProjectileWeaponDamage($attacker),
                $attacker
            );
            $damage_wrapper->setShieldDamageFactor($attacker->getTorpedo()->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getTorpedo()->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damage($damage_wrapper, $target));

            if ($target->getIsDestroyed()) {
                unset($targetPool[$target->getId()]);

                $this->entryCreator->addShipEntry(
                    'Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                    $attacker->getUser()->getId()
                );
                $this->shipRemover->destroy($target);
            }
        }

        return $msg;
    }

    private function getProjectileWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    private function getProjectileWeaponDamage(ShipInterface $ship): float
    {
        $variance = (int) round($ship->getTorpedo()->getBaseDamage() / 100 * $ship->getTorpedo()->getVariance());
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(),
            false,
            $ship->getTorpedo()->getBaseDamage()
        );
        $damage = rand($basedamage - $variance, $basedamage + $variance);
        if (rand(1, 100) <= $ship->getTorpedo()->getCriticalChance()) {
            return $damage * 2;
        }
        return $damage;
    }
}
