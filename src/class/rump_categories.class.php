<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id: rump_categories.class.php 483 2010-03-13 17:31:41Z wolverine $ */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpsCategoryData extends BaseTable { #{{{

	const tablename = 'stu_rumps_categories';
	protected $tablename = 'stu_rumps_categories';

	/**
	 */
	function __construct(&$data) { #{{{
		$this->data = $data;
	} # }}}

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

	/**
	 */
	public function setPoints($value) { # {{{
		$this->setFieldValue('points',$value,'getPoints');
	} # }}}

	/**
	 */
	public function getPoints() { # {{{
		return $this->data['points'];
	} # }}}
	
} #}}}

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @version $Revision: 1.4 $
 * @access public
 */
class RumpsCategory extends RumpsCategoryData { #{{{

	function __construct($id=0) { # {{{
		$result = DB()->query("SELECT * FROM ".self::tablename." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	} # }}}
} #}}}

?>
