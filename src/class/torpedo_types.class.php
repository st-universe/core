<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id: php_snippets.vim,v 1.4 2009-05-12 13:13:42 frick Exp $ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class TorpedoTypeData extends BaseTable { #{{{

	const tablename = 'stu_torpedo_types';
	protected $tablename = 'stu_torpedo_types';
	private $costs;

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setName($value) { # {{{
		$this->setFieldValue('name',$value,'getName');
	} # }}}

	/**
	 */
	public function getName() { # {{{
		return $this->data['name'];
	} # }}}

	/**
	 */
	public function setBaseDamage($value) { # {{{
		$this->setFieldValue('base_damage',$value,'getBaseDamage');
	} # }}}

	/**
	 */
	public function getBaseDamage() { # {{{
		return $this->data['base_damage'];
	} # }}}

	/**
	 */
	public function setCriticalChance($value) { # {{{
		$this->setFieldValue('critical_chance',$value,'getCriticalChance');
	} # }}}

	/**
	 */
	public function getCriticalChance() { # {{{
		return $this->data['critical_chance'];
	} # }}}

	/**
	 */
	public function setHitFactor($value) { # {{{
		$this->setFieldValue('hit_factor',$value,'getHitFactor');
	} # }}}

	/**
	 */
	public function getHitFactor() { # {{{
		return $this->data['hit_factor'];
	} # }}}

	/**
	 */
	public function setShieldDamageFactor($value) { # {{{
		$this->setFieldValue('shield_damage_factor',$value,'getShieldDamageFactor');
	} # }}}

	/**
	 */
	public function getShieldDamageFactor() { # {{{
		return $this->data['shield_damage_factor'];
	} # }}}

	/**
	 */
	public function setHullDamageFactor($value) { # {{{
		$this->setFieldValue('hull_damage_factor',$value,'getHullDamageFactor');
	} # }}}

	/**
	 */
	public function getHullDamageFactor() { # {{{
		return $this->data['hull_damage_factor'];
	} # }}}

	/**
	 */
	public function setVariance($value) { # {{{
		$this->setFieldValue('variance',$value,'getVariance');
	} # }}}

	/**
	 */
	public function getVariance() { # {{{
		return $this->data['variance'];
	} # }}}

	/**
	 */
	public function setGoodId($value) { # {{{
		$this->setFieldValue('good_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['good_id'];
	} # }}}
	
	/**
	 */
	public function setType($value) { # {{{
		$this->setFieldValue('type',$value,'getType');
	} # }}}

	/**
	 */
	public function getType() { # {{{
		return $this->data['type'];
	} # }}}

	/**
	 * @return TorpedoCost[]
	 */
	public function getCosts() { #{{{
		if ($this->costs === NULL) {
			$this->costs = TorpedoCost::getObjectsBy('WHERE torpedo_type_id='.$this->getId());
		}
		return $this->costs;
	} # }}}

	/**
	 */
	public function setEcost($value) { # {{{
		$this->setFieldValue('ecost',$value,'getEcost');
	} # }}}

	/**
	 */
	public function getEcost() { # {{{
		return $this->data['ecost'];
	} # }}}

	/**
	 */
	public function setAmount($value) { # {{{
		$this->setFieldValue('amount',$value,'getAmount');
	} # }}}

	/**
	 */
	public function getAmount() { # {{{
		return $this->data['amount'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class TorpedoType extends TorpedoTypeData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function getObjectsBy($sql="") { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'TorpedoTypeData');
	} # }}}

	/**
	 * @return TorpedoType[]
	 */
	static function getBuildableTorpedoTypesByUser($user_id) { #{{{
		return self::getObjectsBy('WHERE research_id=0 OR research_id IN (SELECT research_id FROM stu_researched WHERE user_id='.$user_id.' AND aktiv=0)');
	} # }}}

} #}}}

?>
