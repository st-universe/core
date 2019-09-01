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
class CrewTrainingData extends BaseTable { #{{{

	const tablename = 'stu_crew_training';
	protected $tablename = 'stu_crew_training';

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
	 * @return UserData
	 */
	public function getUser() { #{{{
		return ResourceCache()->getObject(CACHE_USER,$this->getUserId());
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
	 * @return ColonyData
	 */
	public function getColony() { #{{{
		return ResourceCache()->getObject(CACHE_COLONY,$this->getColonyId());
	} # }}}
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class CrewTraining extends CrewTrainingData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function countInstances($qry='') { #{{{
		return DB()->query("SELECT COUNT(*) FROM ".self::tablename." ".$qry,1);
	} # }}}

	/**
	 * @return CrewTrainingData[]
	 */
	static function getObjectsBy($qry="") {
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$qry);
		return self::_getList($result,'CrewTrainingData');
	}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query("DELETE FROM ".self::tablename." ".$sql);
	} # }}}

} #}}}


?>
