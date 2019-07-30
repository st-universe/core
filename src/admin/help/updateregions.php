<?php

include_once(__DIR__.'/../../inc/config.inc.php');

$result = DB()->query("SELECT * FROM stu_map where region_id>0");
while($data = mysqli_fetch_assoc($result)) {
	$field = new MapFieldType($data['field_id']);
	if (DB()->query("SELECT id FROM stu_map_ftypes WHERE type=".$field->getType()." AND region_id=".$data['region_id'],1) == 0) {
		echo "Feld ".$data['id']." legt Feldtyp ".$field->getType()." an<br />";
		$newfield = new MapFieldTypeData;
		$newfield->setName($field->getName());
		$newfield->setType($field->getType());
		$newfield->setEpsCost($field->getEpsCost());
		$newfield->setIsSystem($field->getIsSystem());
		$newfield->setEpsCost($field->getEpsCost());
		$newfield->setColonyClass($field->getColonyClass());
		$newfield->setDamage($field->getDamage());
		$newfield->setSpecialDamage($field->getSpecialDamage());
		$newfield->setRegionId($data['region_id']);
		$newfield->save();
	}
}
echo "<br /><br />";
$result = DB()->query("SELECT * FROM stu_map where region_id>0");
while($data = mysqli_fetch_assoc($result)) {
	$field = new MapFieldType($data['field_id']);
	if ($field->getRegionId() == 0) {
		$real = DB()->query("SELECT id FROM stu_map_ftypes WHERE type=".$field->getType()." AND region_id=".$data['region_id'],1);
		echo "Feld ".$data['id']." setzt Feldtyp auf ".$real."<br />";
		DB()->query('UPDATE stu_map SET field_id='.$real.' where id='.$data['id']);
	}
}
?>
