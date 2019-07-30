<?php

include_once(__DIR__.'/../../inc/config.inc.php');

class FieldNameDefineGenerator extends DefaultGenerator {

	function __construct() {
		parent::__construct();
		$this->writeSuffix();
	}

	protected $file = 'fieldtypesname.inc.php';

	protected function handle() {
		$this->write('function getFieldName($value) {');
		$this->write('switch ($value) {');
	
		$result = DB()->query("SELECT * FROM stu_colonies_fieldtypes");
		while($data = mysqli_fetch_assoc($result)) {
			$this->write("case ".$data['field_id'].":");
			$this->write("return _('".$data['description']."');");
		}
		
		$this->write("}");
		$this->write("}");
	}
}

new FieldNameDefineGenerator;
?>
