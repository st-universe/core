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
class TorpedoCostData extends BaseTable { #{{{

	protected $tablename = 'stu_torpedo_cost';
	const tablename = 'stu_torpedo_cost';

	/**
	 */
	public function setTorpedoTypeId($value) { # {{{
		$this->setFieldValue('torpedo_type_id',$value,'getTorpedoTypeId');
	} # }}}

	/**
	 */
	public function getTorpedoTypeId() { # {{{
		return $this->data['torpedo_type_id'];
	} # }}}

	/**
	 */
	public function setGoodId($value) { # {{{
		$this->setFieldValue('good_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['good_id'];
	} # }}}

	/**
	 */
	public function setCount($value) { # {{{
		$this->setFieldValue('count',$value,'getCount');
	} # }}}

	/**
	 */
	public function getCount() { # {{{
		return $this->data['count'];
	} # }}}

	/**
	 */
	public function getGood() { #{{{
		return ResourceCache()->getObject(CACHE_GOOD,$this->getGoodId());
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class TorpedoCost extends TorpedoCostData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function getObjectsBy($sql='') { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' '.$sql);
		return self::_getList($result,'TorpedoCostData');
	} # }}}

} #}}}


?>
