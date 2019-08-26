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
class ModuleCostData extends BaseTable { #{{{

	const tablename = 'stu_modules_cost';
	protected $tablename = 'stu_modules_cost';

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
	public function setGoodId($value) { # {{{
		$this->setFieldValue('good_id',$value,'getGoodId');
	} # }}}

	/**
	 */
	public function getGoodId() { # {{{
		return $this->data['good_id'];
	} # }}}

	/**
	 * @return Good
	 */
	public function getGood() { #{{{
		return ResourceCache()->getObject(CACHE_GOOD,$this->getGoodId());
	} # }}}

	/**
	 */
	public function setCount($value) { # {{{
		$this->setFieldValue('count',$value,'getCount');
	} # }}}

	public function getAmount() {
		return $this->data['count'];
	}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleCost extends ModuleCostData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}
	
	/**
	 */
	static function getByModule($module_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE module_id='.$module_id);
		return self::_getList($result,'ModuleCostData');
	} # }}}

} #}}}

?>
