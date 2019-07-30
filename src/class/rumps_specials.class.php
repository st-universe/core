<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpsSpecialsData extends BaseTable { #{{{

	const tablename = 'stu_rumps_specials';
	protected $tablename = 'stu_rumps_specials';

	function __construct(&$data = array()) {
		$this->data = $data;
	}
	
	/**
	 */
	public function setRumpsId($value) { # {{{
		$this->setFieldValue('rumps_id',$value,'getRumpsId');
	} # }}}

	/**
	 */
	public function getRumpsId() { # {{{
		return $this->data['rumps_id'];
	} # }}}

	/**
	 */
	public function setSpecial($value) { # {{{
		$this->setFieldValue('special',$value,'getSpecial');
	} # }}}

	/**
	 */
	public function getSpecial() { # {{{
		return $this->data['special'];
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpsSpecials extends RumpsSpecialsData { #{{{

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function countInstances($sql) { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".parent::tablename." ".$sql,1);
	} # }}}
} #}}}

?>
