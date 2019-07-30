<?php

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class FieldBuildingData extends BaseTable { #{{{

	const tablename = 'stu_field_build';
	protected $tablename = 'stu_field_build';
	
	/**
	 */
	function __construct(&$data=array()) { #{{{
		$this->data = $data;
	} # }}}

	/**
	 */
	public function setType($value) { # {{{
		$this->setFieldValue('type',$value,'getType');
	} # }}}

	/**
	 */
	public function getType() { # {{{
		return $this->data['type'];
	} # }}}

	/**
	 */
	public function setBuildingId($value) { # {{{
		$this->setFieldValue('buildings_id',$value,'getBuildingId');
	} # }}}

	/**
	 */
	public function getBuildingId() { # {{{
		return $this->data['buildings_id'];
	} # }}}

} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class FieldBuilding extends FieldBuildingData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}
	
	/**
	 */
	static function getObjectsBy($sql) { #{{{
		$result = DB()->query("SELECT * FROM ".self::tablename." ".$sql);
		return self::_getList($result,'FieldBuildingData');
	} # }}}

	/**
	 */
	static function truncate($sql) { #{{{
		DB()->query("DELETE FROM ".self::tablename." ".$sql);
	} # }}}

} #}}}


?>
