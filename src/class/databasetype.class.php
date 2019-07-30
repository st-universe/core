<?php

/*
 *
 * Copyright 2010 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/* $Id:$ */


class DatabaseTypeData extends BaseTable {

	const tablename = 'stu_database_types';
	protected $tablename = 'stu_database_types';

	function __construct(&$data=NULL) {
		$this->data = $data;
	}

	function getId() {
		return $this->data['id'];
	}

	function getDescription() {
		return $this->data['description'];
	}

	function getMacro() {
		return $this->data['macro'];
	}
}
class DatabaseType extends DatabaseTypeData {

	function __construct(&$id=0) {
		$result = DB()->query("SELECT * FROM ".$this->getTable()." WHERE id=".$id." LIMIT 1",4);
		if ($result == 0) {
			new ObjectNotFoundException($id);
		}
		return parent::__construct($result);
	}

}
?>
