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

	/**
	 */
	function __construct($damage, $source = false)
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
	public function getDamageRelative($target, $mode)
	{ #{{{
		if ($mode === DAMAGE_MODE_HULL) {
			return $this->calculateDamageHull($target);
		}
		return $this->calculateDamageShields($target);
	} # }}}

	/**
	 */
	private function calculateDamageShields(ShipInterface $target)
	{ #{{{
		$damage = round($this->getDamage() / 100 * $this->getShieldDamageFactor());
		// paratrinic shields
		if ($this->getSource() && $target->getRump()->getRoleId() == ROLE_TORPEDOSHIP && $this->getSource()->getRump()->getRoleId() != ROLE_PULSESHIP) {
			$damage = round($damage * 0.6);
		}
		if ($damage < $target->getShield()) {
			$this->setDamage(0);
		} else {
			$this->setDamage(round($damage - $target->getShield() / $this->getShieldDamageFactor() * 100));
		}
		return $damage;
	} # }}}

	/**
	 */
	private function calculateDamageHull(ShipInterface $target)
	{ #{{{
		$damage = round($this->getDamage() / 100 * $this->getHullDamageFactor());
		// ablative huell plating
		trigger_error($this->getIsPhaserDamage() . " - " . $target->getRump()->getRoleId() . " - " . ROLE_PHASERSHIP);
		if ($this->getIsPhaserDamage() === true && $target->getRump()->getRoleId() == ROLE_PHASERSHIP) {
			trigger_error('damage before ablative hull plating: ' . $damage);
			$damage = round($damage * 0.6);
			trigger_error('damage after ablative hull plating: ' . $damage);
		}
		return $damage;
	} # }}}

} #}}}


?>
