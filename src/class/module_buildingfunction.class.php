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
class ModuleBuildingFunctionData extends BaseTable { #{{{

	const tablename = 'stu_modules_buildingfunction';
	protected $tablename = 'stu_modules_buildingfunction';
	
	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
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
	public function setBuildingfunction($value) { # {{{
		$this->setFieldValue('buildingfunction',$value,'getBuildingfunction');
	} # }}}

	/**
	 */
	public function getBuildingfunction() { # {{{
		return $this->data['buildingfunction'];
	} # }}}

	/**
	 */
	public function getModule() { #{{{
		return ResourceCache()->getObject('module',$this->getModuleId());
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleBuildingFunction extends ModuleBuildingFunctionData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}
	
	/**
	 */
	static function getByFunctionAndUser($function_id,$user_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE buildingfunction='.$function_id.' AND module_id IN ('.self::getResearchQuery($user_id).') ORDER BY module_id');
		return self::_getList($result,'ModuleBuildingFunctionData','module_id');
	} # }}}

	/**
	 */
	private static function getResearchQuery($user_id) { #{{{
		return 'SELECT id FROM stu_modules WHERE (research_id=0 OR research_id IN (SELECT research_id FROM stu_researched WHERE user_id='.$user_id.' AND aktiv=0))';	
	} # }}}

} #}}}

?>
