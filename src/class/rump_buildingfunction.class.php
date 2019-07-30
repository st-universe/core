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
class RumpBuildingFunctionData extends BaseTable { #{{{

	protected $tablename = 'stu_rumps_buildingfunction';
	const tablename = 'stu_rumps_buildingfunction';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setRumpId($value) { # {{{
		$this->setFieldValue('rump_id',$value,'getRumpId');
	} # }}}

	/**
	 */
	public function getRumpId() { # {{{
		return $this->data['rump_id'];
	} # }}}

	/**
	 */
	public function setBuildingFunction($value) { # {{{
		$this->setFieldValue('building_function',$value,'getBuildingFunction');
	} # }}}

	/**
	 */
	public function getBuildingFunction() { # {{{
		return $this->data['building_function'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpBuildingFunction extends RumpBuildingFunctionData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getByRumpId($rump_id) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE rump_id=".$rump_id);
		return parent::_getList($result,'RumpBuildingFunctionData');
	} # }}}

} #}}}

?>
