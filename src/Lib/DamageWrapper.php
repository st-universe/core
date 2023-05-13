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
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @access public
 */
class DamageWrapper
{
    private float $damage = 0;
    private bool $isCrit = false;
    private int $modificator = 100;

    public function __construct(int $damage)
    {
        $this->damage = $damage;
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

    public function setDamage(float $value): void
    {
        $this->damage = $value;
    }

    public function getDamage(): float
    {
        return $this->damage;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $value): void
    {
        $this->modificator = $value;
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
        $damage = round($this->getDamage() / 100 * $this->getShieldDamageFactor());

        if ($damage < $target->getShield()) {
            $this->setDamage(0);
        } else {
            $this->setDamage(round($damage - $target->getShield() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    }


    private function calculateDamageColonyShields(ColonyInterface $target): float
    {
        $damage = round($this->getDamage() / 100 * $this->getShieldDamageFactor());

        if ($damage < $target->getShields()) {
            $this->setDamage(0);
        } else {
            $this->setDamage(round($damage - $target->getShields() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    }


    private function calculateDamageHull(): float
    {
        $damage = round($this->getDamage() / 100 * $this->getHullDamageFactor());

        if ($this->getIsTorpedoDamage() === true) {
            $damage = round($damage * ($this->getModificator() / 100));
        }
        return $damage;
    }


    private function calculateDamageBuilding(): float
    {
        return round($this->getDamage() / 100 * $this->getHullDamageFactor());
    }
}
