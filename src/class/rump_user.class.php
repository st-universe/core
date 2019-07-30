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
class RumpUserData extends BaseTable { #{{{

	protected $tablename = 'stu_rumps_user';
	const tablename = 'stu_rumps_user';

	function __construct(&$data=array()) {
		$this->data = $data;
	}

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

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpUser extends RumpUserData { #{{{

	function __construct($id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($crewId);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function countInstances($where) { #{{{
		return parent::getCount(self::tablename,$where);
	} # }}}

	/**
	 */
	static function truncate($sql='') { #{{{
		DB()->query('DELETE FROM '.self::tablename.' '.$sql);
	} # }}}

	/**
	 */
	static function getBy($sql='') { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'RumpUser');
	} # }}}

} #}}}

?>
