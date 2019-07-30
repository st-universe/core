<?php
include_once(__DIR__.'/../../inc/config.inc.php');

$result = SystemType::getObjectsBy('WHERE id NOT IN (SELECT object_id FROM stu_database_entrys WHERE category_id=6)');
foreach ($result as $key => $obj) {
	$db = new DatabaseEntryData;
	$db->setCategoryId(6);
	$db->setDescription($obj->getDescription());
	$db->setSort($obj->getId());
	$db->setObjectId($obj->getId());
	$db->save();
	$obj->setDatabaseId($db->getId());
	$obj->save();
}
?>
