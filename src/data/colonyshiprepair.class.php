<?php

/*
 * Copyright 2011 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 */


/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 */
class ColonyShipRepair extends ColonyShipRepair_Table { # {{{

	/**
	 */
	public function _create() {
		return new ColonyShipRepair;
	}
	private $field = NULL;

	/**
	 */
	public function getField() { #{{{
		if ($this->field === NULL) {
			$this->field = Colfields::getByColonyField($this->getFieldId(),$this->getColonyId());
		}
		return $this->field;
	} # }}}

	/**
	 */
	public function getColony() { #{{{
		return ResourceCache()->getObject(CACHE_COLONY,$this->getColonyId());
	} # }}}

	/**
	 */
	static public function getByColonyField($colony_id,$field_id) { #{{{
		return parent::getObjectsBy('colony_id='.$colony_id.' AND field_id='.$field_id,'id asc');
	} # }}}

	/**
	 */
	public function getShip() { #{{{
		return ResourceCache()->getObject(CACHE_SHIP,$this->getShipId());
	} # }}}

} # }}}

?>
