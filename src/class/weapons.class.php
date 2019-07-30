<?php


/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class WeaponsData extends BaseTable { #{{{
	
	const tablename = 'stu_weapons';
	protected $tablename = 'stu_weapons';

	/**
	 */
	function __construct(&$data = array()) { #{{{
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
	public function setType($value) { # {{{
		$this->setFieldValue('type',$value,'getType');
	} # }}}

	/**
	 */
	public function getType() { # {{{
		return $this->data['type'];
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
	public function setModuleId($value) { # {{{
		$this->setFieldValue('module_id',$value,'getModuleId');
	} # }}}

	/**
	 */
	public function getModuleId() { # {{{
		return $this->data['module_id'];
	} # }}}

	/**
	 */
	public function setFiringMode($value) { # {{{
		$this->setFieldValue('firing_mode',$value,'getFiringMode');
	} # }}}

	/**
	 */
	public function getFiringMode() { # {{{
		return $this->data['firing_mode'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class Weapons extends WeaponsData { #{{{
	
	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getByModuleId($moduleId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE module_id=".intval($moduleId)." LIMIT 1",4);		
		if ($result == 0) {
			return FALSE;
		}
		return new WeaponsData($result);
	} # }}}

} #}}}

?>
