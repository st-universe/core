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
class ModuleQueueData extends BaseTable { #{{{

	const tablename = 'stu_modules_queue';
	protected $tablename = 'stu_modules_queue';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setColonyId($value) { # {{{
		$this->setFieldValue('colony_id',$value,'getColonyId');
	} # }}}

	/**
	 */
	public function getColonyId() { # {{{
		return $this->data['colony_id'];
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
	public function setCount($value) { # {{{
		$this->setFieldValue('count',$value,'getAmount');
	} # }}}

	/**
	 */
	public function getAmount() { # {{{
		return $this->data['count'];
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
     * @return ModulesData
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
class ModuleQueue extends ModuleQueueData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}
	
	/**
	 */
	static function countInstances($sql="") { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	} # }}}

	static function getAmountByColonyAndModule($colony_id,$module_id): int {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE colony_id=".$colony_id." AND module_id=".$module_id,4);
		if ($result == 0) {
			return 0;
		}
		return (int) $result['count'];
	}

	/**
	 */
	static function getBy($sql="") { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql." LIMIT 1",4);
		if ($result == 0) {
			return FALSE;
		}
		return new ModuleQueueData($result);
	} # }}}

	/**
	 */
	static function getObjectsBy($sql="") { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'ModuleQueueData');
	} # }}}

} #}}}
