<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

final class EnergyWeaponPhase implements EnergyWeaponPhaseInterface
{

    public const FIRINGMODE_RANDOM = 1;
    public const FIRINGMODE_FOCUS = 2;

    private ShipRepositoryInterface $shipRepository;

    private WeaponRepositoryInterface $weaponRepository;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    private ApplyDamageInterface $applyDamage;

    private ModuleValueCalculatorInterface $moduleValueCalculator;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        WeaponRepositoryInterface $weaponRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ApplyDamageInterface $applyDamage,
        ModuleValueCalculatorInterface $moduleValueCalculator
    ) {
        $this->shipRepository = $shipRepository;
        $this->weaponRepository = $weaponRepository;
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
            $damage_wrapper = new DamageWrapper(
                $this->getEnergyWeaponDamage($attacker),
                $attacker
            );
            $damage_wrapper->setShieldDamageFactor($attacker->getRump()->getPhaserShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($attacker->getRump()->getPhaserHullDamageFactor());
            $damage_wrapper->setIsPhaserDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damage($damage_wrapper, $target));

            if ($target->getIsDestroyed()) {
                $this->entryCreator->addShipEntry(
                    'Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                    $attacker->getUser()->getId()
                );

                $this->shipRemover->destroy($target);

                unset($targetPool[$target->getId()]);

                if ($this->getEnergyWeapon($attacker)->getFiringMode() === self::FIRINGMODE_FOCUS) {
                    break;
                }
            }
        }

        return $msg;
    }

    private function getEnergyWeaponDamage(ShipInterface $ship): float
    {
        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            return 0;
        }
        $basedamage = $this->moduleValueCalculator->calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModule(),
            'getBaseDamage'
        );
        $variance = (int)round($basedamage / 100 * $this->getEnergyWeapon($ship)->getVariance());
        $damage = rand($basedamage - $variance, $basedamage + $variance);
        if (rand(1, 100) <= $this->getEnergyWeapon($ship)->getCriticalChance()) {
            return $damage * 2;
        }
        return $damage;
    }

    private function getEnergyWeapon(ShipInterface $ship): ?WeaponInterface
    {
        return $this->weaponRepository->findByModule(
            (int)$ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModuleId()
        );
    }

    public function getEnergyWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }
}
