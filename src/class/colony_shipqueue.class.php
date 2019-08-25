<?php

class ColonyShipQueueData extends BaseTable { #{{{

	const tablename = 'stu_colonies_shipqueue';
	protected $tablename = 'stu_colonies_shipqueue';

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
	public function setUserId($value) { # {{{
		$this->setFieldValue('user_id',$value,'getUserId');
	} # }}}

	/**
	 */
	public function getUserId() { # {{{
		return $this->data['user_id'];
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
	public function setBuildtime($value) { # {{{
		$this->setFieldValue('buildtime',$value,'getBuildtime');
	} # }}}

	/**
	 */
	public function getBuildtime() { # {{{
		return $this->data['buildtime'];
	} # }}}

	/**
	 */
	public function setFinishDate($value) { # {{{
		$this->setFieldValue('finish_date',$value,'getFinishDate');
	} # }}}

	/**
	 */
	public function getFinishDate() { # {{{
		return $this->data['finish_date'];
	} # }}}

	/**
	 */
	public function getRump() { #{{{
		return new Shiprump($this->getRumpId());
	} # }}}

	/**
	 */
	public function setCrew($value) { # {{{
		$this->setFieldValue('crew',$value,'getCrew');
	} # }}}

	/**
	 */
	public function getCrew() { # {{{
		return $this->data['crew'];
	} # }}}

	/**
	 */
	public function setStopDate($value) { # {{{
		$this->setFieldValue('stop_date',$value,'getStopDate');
	} # }}}

	/**
	 */
	public function getStopDate() { # {{{
		return $this->data['stop_date'];
	} # }}}

	/**
	 */
	public function setBuildingFunctionId($value) { # {{{
		$this->setFieldValue('building_function_id',$value,'getBuildingFunctionId');
	} # }}}

	/**
	 */
	public function getBuildingFunctionId() { # {{{
		return $this->data['building_function_id'];
	} # }}}
	
	/**
	 */
	public function isStopped() { #{{{
		return $this->getStopDate() > 0;
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ColonyShipQueue extends ColonyShipQueueData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function getFinishedJobs() { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE finish_date<=".time()." AND stop_date=0");
		return self::_getList($result,'ColonyShipQueueData');
	} # }}}

	/**
	 */
	static function getByColonyId($colonyId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE colony_id=".intval($colonyId));
		return self::_getList($result,'ColonyShipQueueData');
	} # }}}

	/**
	 */
	static function getByUserId($user_id) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE user_id=".intval($user_id));
		return self::_getList($result,'ColonyShipQueueData');
	} # }}}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' WHERE '.$sql);
	} # }}}

	/**
	 */
	static function countInstances($sql='') { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$sql,1);
	} # }}}

	/**
	 */
	static function stopBuildProcess($colony_id,$building_function_id) { #{{{
		DB()->query("UPDATE ".self::tablename." SET stop_date=UNIX_TIMESTAMP() WHERE colony_id=".$colony_id." AND building_function_id=".$building_function_id);
	} # }}}

	/**
	 */
	static function restartBuildProcess($colony_id,$building_function_id) { #{{{
		DB()->query("UPDATE ".self::tablename." SET finish_date=UNIX_TIMESTAMP()+(finish_date-stop_date),stop_date=0 WHERE colony_id=".$colony_id." AND building_function_id=".$building_function_id);
	} # }}}

} #}}}

?>
