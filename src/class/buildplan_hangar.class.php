<?php

class BuildplanHangarData extends BaseTable { #{{{

	const tablename = 'stu_buildplans_hangar';
	protected $tablename = 'stu_buildplans_hangar';
	
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
	public function setBuildplanId($value) { # {{{
		$this->setFieldValue('buildplan_id',$value,'getBuildplanId');
	} # }}}

	/**
	 */
	public function getBuildplanId() { # {{{
		return $this->data['buildplan_id'];
	} # }}}
	
	private $buildplan = NULL;

	/**
	 */
	public function getBuildplan() { #{{{
		if ($this->buildplan === NULL) {
			$this->buildplan = new ShipBuildplans($this->getBuildplanId());
		}
		return $this->buildplan;
	} # }}}

	/**
	 */
	public function setDefaultTorpedoTypeId($value) { # {{{
		$this->setFieldValue('default_torpedo_type_id',$value,'getDefaultTorpedoTypeId');
	} # }}}

	/**
	 */
	public function getDefaultTorpedoTypeId() { # {{{
		return $this->data['default_torpedo_type_id'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class BuildplanHangar extends BuildplanHangarData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}
	
	/**
	 */
	static function getBy($sql) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql,4);
		if ($result == 0) {
			return FALSE;
		}
		return new BuildplanHangarData($result);
	} # }}}

} #}}}

?>
