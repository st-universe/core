<?php

class BuildPlanModulesData extends BaseTable { #{{{

	const tablename = 'stu_buildplans_modules';
	protected $tablename = 'stu_buildplans_modules';

	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setBuildplanId($value) { # {{{
		$this->setFieldValue('buildplan_id',$value,'getBuildplanId');
	} # }}}

	/**
	 */
	public function getBuildplanId() { # {{{
		return $this->data['buildplan_id'];
	} # }}}

	/**
	 */
	public function setModuleType($value) { # {{{
		$this->setFieldValue('module_type',$value,'getModuleType');
	} # }}}

	/**
	 */
	public function getModuleType() { # {{{
		return $this->data['module_type'];
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
	public function getModule() { #{{{
		return ResourceCache()->getObject('module',$this->getModuleId());
	} # }}}

	/**
	 */
	public function deepDelete() { #{{{
		$this->deleteFromDatabase();
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class BuildPlanModules extends BuildPlanModulesData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function insertFromBuildProcess($planId,$modules) { #{{{
		foreach($modules as $type => $obj) {
			$mod = new BuildPlanModulesData;
			$mod->setModuleType($obj->getType());
			$mod->setBuildplanId($planId);
			$mod->setModuleId($obj->getId());
			$mod->save();
		}
	} # }}}

	/**
	 */
	static function getByType($planId,$type) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE buildplan_id=".intval($planId)." AND module_type=".intval($type));;	
		return self::_getList($result,'BuildPlanModulesData','module_id');
	} # }}}

	/**
	 */
	static function getByBuildplan($planId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE buildplan_id=".intval($planId));	
		return self::_getList($result,'BuildPlanModulesData');
	} # }}}

} #}}}

?>
