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
use Stu\Component\Ship\ShipRoleEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class DamageWrapper
{ #{{{
    private $damage = 0;
    private $source = false;
    private $isCrit = false;

    /**
     */
    public function __construct($damage, $source = false)
    { #{{{
        $this->damage = $damage;
        $this->source = $source;
    } # }}}

    private $hull_damage_factor = 100;

    /**
     */
    public function setHullDamageFactor($value)
    { #{{{
        $this->hull_damage_factor = $value;
    } # }}}

    /**
     */
    public function getHullDamageFactor()
    { #{{{
        return $this->hull_damage_factor;
    } # }}}

    public function setCrit(bool $isCrit)
    {
        $this->isCrit = $isCrit;
    }

    public function isCrit(): bool
    {
        return $this->isCrit;
    }

    private $shield_damage_factor = 100;

    /**
     */
    public function setShieldDamageFactor($value)
    { #{{{
        $this->shield_damage_factor = $value;
    } # }}}

    /**
     */
    public function getShieldDamageFactor()
    { #{{{
        return $this->shield_damage_factor;
    } # }}}

    private $is_phaser_damage = false;

    /**
     */
    public function setIsPhaserDamage($value)
    { #{{{
        $this->is_phaser_damage = $value;
    } # }}}

    /**
     */
    public function getIsPhaserDamage()
    { #{{{
        return $this->is_phaser_damage;
    } # }}}

    private $is_torpedo_damage = false;

    /**
     */
    public function setIsTorpedoDamage($value)
    { #{{{
        $this->is_torpedo_damage = $value;
    } # }}}

    /**
     */
    public function getIsTorpedoDamage()
    { #{{{
        return $this->is_torpedo_damage;
    } # }}}

    /**
     */
    public function getSource()
    { #{{{
        return $this->source;
    } # }}}

    /**
     */
    public function setDamage($value)
    { #{{{
        $this->damage = $value;
    } # }}}

    /**
     */
    public function getDamage()
    { #{{{
        return $this->damage;
    } # }}}

    /**
     */
    public function getDamageRelative($target, $mode, $isColony = false)
    { #{{{
        if ($isColony) {
            if ($mode === ShipEnum::DAMAGE_MODE_HULL) {
                return $this->calculateDamageBuilding();
            }
            return $this->calculateDamageColonyShields($target);
        }
        if ($mode === ShipEnum::DAMAGE_MODE_HULL) {
            return $this->calculateDamageHull($target);
        }
        return $this->calculateDamageShields($target);
    } # }}}

    /**
     */
    private function calculateDamageShields(ShipInterface $target)
    { #{{{
        $damage = round($this->getDamage() / 100 * $this->getShieldDamageFactor());
        /* paratrinic shields
        if ($this->getSource() && $target->getRump()->getRoleId() == ShipRoleEnum::ROLE_TORPEDOSHIP && $this->getSource()->getRump()->getRoleId() != ShipRoleEnum::ROLE_PULSESHIP) {
            $damage = round($damage * 0.6);
        } */
        if ($damage < $target->getShield()) {
            $this->setDamage(0);
        } else {
            $this->setDamage(round($damage - $target->getShield() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    } # }}}

    /**
     */
    private function calculateDamageColonyShields(ColonyInterface $target)
    { #{{{
        $damage = round($this->getDamage() / 100 * $this->getShieldDamageFactor());
        // paratrinic shields
        if ($damage < $target->getShields()) {
            $this->setDamage(0);
        } else {
            $this->setDamage(round($damage - $target->getShields() / $this->getShieldDamageFactor() * 100));
        }
        return $damage;
    } # }}}

    /**
     */
    private function calculateDamageHull(ShipInterface $target)
    { #{{{
        $damage = round($this->getDamage() / 100 * $this->getHullDamageFactor());
        /* ablative huell plating
         if ($this->getIsPhaserDamage() === true && $target->getRump()->getRoleId() == ShipRoleEnum::ROLE_PHASERSHIP) {
            $damage = round($damage * 0.6);
        } */
        return $damage;
    } # }}}

    /**
     */
    private function calculateDamageBuilding()
    { #{{{
        $damage = round($this->getDamage() / 100 * $this->getHullDamageFactor());
        return $damage;
    } # }}}
} #}}}
