<?php

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpRoleData extends BaseTable { #{{{

	const tablename = 'stu_rumps_roles';
	protected $tablename = 'stu_rumps_roles';

	/**
	 */
	public function setName($value) { # {{{
		$this->setFieldValue('name',$value,'getName');
	} # }}}

	/**
	 */
	public function getName() { # {{{
		return $this->data['name'];
	} # }}}

	/**
	 */
	public function setDatabaseId($value) { # {{{
		$this->setFieldValue('database_id',$value,'getDatabaseId');
	} # }}}

	/**
	 */
	public function getDatabaseId() { # {{{
		return $this->data['database_id'];
	} # }}}

	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpRole extends RumpRoleData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}
} #}}}

?>
