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
class RumpModuleLevelData extends BaseTable { #{{{

	protected $tablename = 'stu_rumps_module_level';
	const tablename = 'stu_rumps_module_level';

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
	public function setModuleLevel1($value) { # {{{
		$this->setFieldValue('module_level_1',$value,'getModuleLevel1');
	} # }}}

	/**
	 */
	public function getModuleLevel1() { # {{{
		return $this->data['module_level_1'];
	} # }}}

	/**
	 */
	public function setModuleLevel2($value) { # {{{
		$this->setFieldValue('module_level_2',$value,'getModuleLevel2');
	} # }}}

	/**
	 */
	public function getModuleLevel2() { # {{{
		return $this->data['module_level_2'];
	} # }}}

	/**
	 */
	public function setModuleLevel3($value) { # {{{
		$this->setFieldValue('module_level_3',$value,'getModuleLevel3');
	} # }}}

	/**
	 */
	public function getModuleLevel3() { # {{{
		return $this->data['module_level_3'];
	} # }}}

	/**
	 */
	public function setModuleLevel4($value) { # {{{
		$this->setFieldValue('module_level_4',$value,'getModuleLevel4');
	} # }}}

	/**
	 */
	public function getModuleLevel4() { # {{{
		return $this->data['module_level_4'];
	} # }}}

	/**
	 */
	public function setModuleLevel5($value) { # {{{
		$this->setFieldValue('module_level_5',$value,'getModuleLevel5');
	} # }}}

	/**
	 */
	public function getModuleLevel5() { # {{{
		return $this->data['module_level_5'];
	} # }}}

	/**
	 */
	public function setModuleLevel6($value) { # {{{
		$this->setFieldValue('module_level_6',$value,'getModuleLevel6');
	} # }}}

	/**
	 */
	public function getModuleLevel6() { # {{{
		return $this->data['module_level_6'];
	} # }}}

	/**
	 */
	public function setModuleLevel7($value) { # {{{
		$this->setFieldValue('module_level_7',$value,'getModuleLevel7');
	} # }}}

	/**
	 */
	public function getModuleLevel7() { # {{{
		return $this->data['module_level_7'];
	} # }}}

	/**
	 */
	public function setModuleLevel8($value) { # {{{
		$this->setFieldValue('module_level_8',$value,'getModuleLevel8');
	} # }}}

	/**
	 */
	public function getModuleLevel8() { # {{{
		return $this->data['module_level_8'];
	} # }}}

	/**
	 */
	public function setModuleMandatory1($value) { # {{{
		$this->setFieldValue('module_mandatory_1',$value,'getModuleMandatory1');
	} # }}}

	/**
	 */
	public function getModuleMandatory1() { # {{{
		return $this->data['module_mandatory_1'];
	} # }}}

	/**
	 */
	public function setModuleMandatory2($value) { # {{{
		$this->setFieldValue('module_mandatory_2',$value,'getModuleMandatory2');
	} # }}}

	/**
	 */
	public function getModuleMandatory2() { # {{{
		return $this->data['module_mandatory_2'];
	} # }}}

	/**
	 */
	public function setModuleMandatory3($value) { # {{{
		$this->setFieldValue('module_mandatory_3',$value,'getModuleMandatory3');
	} # }}}

	/**
	 */
	public function getModuleMandatory3() { # {{{
		return $this->data['module_mandatory_3'];
	} # }}}

	/**
	 */
	public function setModuleMandatory4($value) { # {{{
		$this->setFieldValue('module_mandatory_4',$value,'getModuleMandatory4');
	} # }}}

	/**
	 */
	public function getModuleMandatory4() { # {{{
		return $this->data['module_mandatory_4'];
	} # }}}

	/**
	 */
	public function setModuleMandatory5($value) { # {{{
		$this->setFieldValue('module_mandatory_5',$value,'getModuleMandatory5');
	} # }}}

	/**
	 */
	public function getModuleMandatory5() { # {{{
		return $this->data['module_mandatory_5'];
	} # }}}

	/**
	 */
	public function setModuleMandatory6($value) { # {{{
		$this->setFieldValue('module_mandatory_6',$value,'getModuleMandatory6');
	} # }}}

	/**
	 */
	public function getModuleMandatory6() { # {{{
		return $this->data['module_mandatory_6'];
	} # }}}

	/**
	 */
	public function setModuleMandatory7($value) { # {{{
		$this->setFieldValue('module_mandatory_7',$value,'getModuleMandatory7');
	} # }}}

	/**
	 */
	public function getModuleMandatory7() { # {{{
		return $this->data['module_mandatory_7'];
	} # }}}

	/**
	 */
	public function setModuleMandatory8($value) { # {{{
		$this->setFieldValue('module_mandatory_8',$value,'getModuleMandatory8');
	} # }}}

	/**
	 */
	public function getModuleMandatory8() { # {{{
		return $this->data['module_mandatory_8'];
	} # }}}

	/**
	 */
	public function setModuleLevel1Min($value) { # {{{
		$this->setFieldValue('module_level_1_min',$value,'getModuleLevel1Min');
	} # }}}

	/**
	 */
	public function getModuleLevel1Min() { # {{{
		return $this->data['module_level_1_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel1Max($value) { # {{{
		$this->setFieldValue('module_level_1_max',$value,'getModuleLevel1Max');
	} # }}}

	/**
	 */
	public function getModuleLevel1Max() { # {{{
		return $this->data['module_level_1_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel2Min($value) { # {{{
		$this->setFieldValue('module_level_2_min',$value,'getModuleLevel2Min');
	} # }}}

	/**
	 */
	public function getModuleLevel2Min() { # {{{
		return $this->data['module_level_2_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel2Max($value) { # {{{
		$this->setFieldValue('module_level_2_max',$value,'getModuleLevel2Max');
	} # }}}

	/**
	 */
	public function getModuleLevel2Max() { # {{{
		return $this->data['module_level_2_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel3Min($value) { # {{{
		$this->setFieldValue('module_level_3_min',$value,'getModuleLevel3Min');
	} # }}}

	/**
	 */
	public function getModuleLevel3Min() { # {{{
		return $this->data['module_level_3_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel3Max($value) { # {{{
		$this->setFieldValue('module_level_3_max',$value,'getModuleLevel3Max');
	} # }}}

	/**
	 */
	public function getModuleLevel3Max() { # {{{
		return $this->data['module_level_3_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel4Min($value) { # {{{
		$this->setFieldValue('module_level_4_min',$value,'getModuleLevel4Min');
	} # }}}

	/**
	 */
	public function getModuleLevel4Min() { # {{{
		return $this->data['module_level_4_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel4Max($value) { # {{{
		$this->setFieldValue('module_level_4_max',$value,'getModuleLevel4Max');
	} # }}}

	/**
	 */
	public function getModuleLevel4Max() { # {{{
		return $this->data['module_level_4_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel5Min($value) { # {{{
		$this->setFieldValue('module_level_5_min',$value,'getModuleLevel5Min');
	} # }}}

	/**
	 */
	public function getModuleLevel5Min() { # {{{
		return $this->data['module_level_5_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel5Max($value) { # {{{
		$this->setFieldValue('module_level_5_max',$value,'getModuleLevel5Max');
	} # }}}

	/**
	 */
	public function getModuleLevel5Max() { # {{{
		return $this->data['module_level_5_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel6Min($value) { # {{{
		$this->setFieldValue('module_level_6_min',$value,'getModuleLevel6Min');
	} # }}}

	/**
	 */
	public function getModuleLevel6Min() { # {{{
		return $this->data['module_level_6_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel6Max($value) { # {{{
		$this->setFieldValue('module_level_6_max',$value,'getModuleLevel6Max');
	} # }}}

	/**
	 */
	public function getModuleLevel6Max() { # {{{
		return $this->data['module_level_6_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel7Min($value) { # {{{
		$this->setFieldValue('module_level_7_min',$value,'getModuleLevel7Min');
	} # }}}

	/**
	 */
	public function getModuleLevel7Min() { # {{{
		return $this->data['module_level_7_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel7Max($value) { # {{{
		$this->setFieldValue('module_level_7_max',$value,'getModuleLevel7Max');
	} # }}}

	/**
	 */
	public function getModuleLevel7Max() { # {{{
		return $this->data['module_level_7_max'];
	} # }}}

	/**
	 */
	public function setModuleLevel8Min($value) { # {{{
		$this->setFieldValue('module_level_8_min',$value,'getModuleLevel8Min');
	} # }}}

	/**
	 */
	public function getModuleLevel8Min() { # {{{
		return $this->data['module_level_8_min'];
	} # }}}

	/**
	 */
	public function setModuleLevel8Max($value) { # {{{
		$this->setFieldValue('module_level_8_max',$value,'getModuleLevel8Max');
	} # }}}

	/**
	 */
	public function getModuleLevel8Max() { # {{{
		return $this->data['module_level_8_max'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpModuleLevel extends RumpModuleLevelData { #{{{

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

	/**
	 */
	static function getByRump($rump_id) { #{{{
		$result = DB()->query('SELECT * FROM '.self::tablename.' WHERE rump_id='.$rump_id,4);
		return new RumpModuleLevelData($result);
	} # }}}

} #}}}

?>
