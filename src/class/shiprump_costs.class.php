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
class ShipRumpCostsData extends BaseTable { #{{{

	const tablename = 'stu_rump_costs';
	protected $tablename = 'stu_rump_costs';
	
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

	public function getAmount() {
		return $this->data['count'];
	}

	/**
	 */
	public function getGood() { #{{{
		return ResourceCache()->getObject('good',$this->getGoodId());
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ShipRumpCosts extends ShipRumpCostsData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}
	
	/**
	 */
	static function getByRump($rumpId) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE rump_id=".intval($rumpId));
		return self::_getList($result,'ShipRumpCostsData');
	} # }}}

} #}}}


?>
