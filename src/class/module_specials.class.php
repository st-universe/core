<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id: module_specials.class.php 462 2010-03-13 16:17:30Z wolverine $ */

define('MODULE_SPECIAL_CLOAK',1);

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSpecialData extends BaseTable { #{{{

	const tablename = 'stu_modules_specials';
	protected $tablename = 'stu_modules_specials';

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
	public function setSpecialId($value) { # {{{
		$this->setFieldValue('special_id',$value,'getSpecialId');
	} # }}}

	/**
	 */
	public function getSpecialId() { # {{{
		return $this->data['special_id'];
	} # }}}

	/**
	 */
	public function getName() { #{{{
		switch ($this->getSpecialId()) {
			case MODULE_SPECIAL_CLOAK:
				return _('Tarnung');
		}
		return '';
	} # }}}

	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class ModuleSpecial extends ModuleSpecialData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}

	/**
	 */
	static function countInstances($module_id,$special_id) { #{{{
		return DB()->query('SELECT COUNT(*) FROM '.self::tablename.' WHERE module_id='.$module_id.' AND special_id='.$special_id,1);
	} # }}}

	/**
	 */
	static function getBy($qry) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE ".$qry);
		return self::_getList($result,'ModuleSpecialData');
	} # }}}
} #}}}

?>
