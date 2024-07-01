<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

namespace Stu\Lib;

use Stu\Component\Ship\ShipEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @access public
 */
class DamageWrapper
{
    private float $netDamage = 0;
    private bool $isCrit = false;
    private bool $isShieldPenetration = false;
    private int $modificator = 100;
    private ?int $pirateWrath = null;

    public function __construct(int $netDamage)
    {
        $this->netDamage = $netDamage;
    }

    private int $hull_damage_factor = 100;


    public function setHullDamageFactor(int $value): void
    {
        $this->hull_damage_factor = $value;
    }


    public function getHullDamageFactor(): int
    {
        return $this->hull_damage_factor;
    }

    public function setCrit(bool $isCrit): void
    {
        $this->isCrit = $isCrit;
    }

    public function isCrit(): bool
    {
        return $this->isCrit;
    }

    public function setShieldPenetration(bool $isShieldPenetration): void
    {
        $this->isShieldPenetration = $isShieldPenetration;
    }

    public function isShieldPenetration(): bool
    {
        return $this->isShieldPenetration;
    }

    private int $shield_damage_factor = 100;


    public function setShieldDamageFactor(int $value): void
    {
        $this->shield_damage_factor = $value;
    }


    public function getShieldDamageFactor(): int
    {
        return $this->shield_damage_factor;
    }

    private bool $is_phaser_damage = false;


    public function setIsPhaserDamage(bool $value): void
    {
        $this->is_phaser_damage = $value;
    }


    public function getIsPhaserDamage(): bool
    {
        return $this->is_phaser_damage;
    }

    private bool $is_torpedo_damage = false;


    public function setIsTorpedoDamage(bool $value): void
    {
        $this->is_torpedo_damage = $value;
    }


    public function getIsTorpedoDamage(): bool
    {
        return $this->is_torpedo_damage;
    }

    public function setNetDamage(float $value): void
    {
        $this->netDamage = $value;
    }

    public function getNetDamage(): float
    {
        return $this->netDamage;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $value): void
    {
        $this->modificator = $value;
    }

    public function setPirateWrath(UserInterface $attacker, ShipInterface $target): void
    {
        if ($attacker->getId() !== UserEnum::USER_NPC_KAZON) {
            return;
        }

        $pirateWrath = $target->getUser()->getPirateWrath();
        if ($pirateWrath === null) {
            return;
        }

        $this->pirateWrath = $pirateWrath->getWrath();
    }

    public function getDamageRelative(ColonyInterface|ShipInterface $target, int $mode): float
    {
        if ($target instanceof ColonyInterface) {
            if ($mode === ShipEnum::DAMAGE_MODE_HULL) {
                return $this->calculateDamageBuilding();
            }
            return $this->calculateDamageColonyShields($target);
        }
        if ($mode === ShipEnum::DAMAGE_MODE_HULL) {
            return $this->calculateDamageHull();
        }

        return $this->calculateDamageShields($target);
    }


    private function calculateDamageShields(ShipInterface $target): float
    {
        $netDamage = $this->getNetDamage();
        $netDamage = $this->mindPirateWrath($netDamage);

        $targetShields = $target->getShield();

        $grossModificator = round($this->getShieldDamageFactor() / 100);
        if ($this->getIsPhaserDamage() === true) {
            $grossModificator = round($grossModificator * $this->modificator / 100);
        }

        $neededNetDamageForShields = min($netDamage, (int)ceil($targetShields / $grossModificator));
        $grossDamage = min($targetShields, $neededNetDamageForShields * $grossModificator);

        if ($neededNetDamageForShields >= $netDamage) {
            $this->setNetDamage(0);
        } else {
            $this->setNetDamage($netDamage - $neededNetDamageForShields);
        }

        return $grossDamage;
    }


    private function calculateDamageColonyShields(ColonyInterface $target): float
    {
        $damage = round($this->getNetDamage() / 100 * $this->getShieldDamageFactor());

        if ($damage < $target->getShields()) {
            $this->setNetDamage(0);
        } else {
            $this->setNetDamage(round($damage - $target->getShields() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    }


    private function calculateDamageHull(): float
    {
        $damage = round($this->getNetDamage() / 100 * $this->getHullDamageFactor());
        $damage = $this->mindPirateWrath($damage);

        if ($this->getIsTorpedoDamage() === true) {
            $damage = round($damage * ($this->getModificator() / 100));
        }
        return $damage;
    }


    private function calculateDamageBuilding(): float
    {
        return round($this->getNetDamage() / 100 * $this->getHullDamageFactor());
    }

    private function mindPirateWrath(float $damage): float
    {
        if ($this->pirateWrath === null) {
            return $damage;
        }

        return round($damage / PirateWrathInterface::DEFAULT_WRATH * $this->pirateWrath);
    }
}
