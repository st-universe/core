<?php

include_once(__DIR__.'/../../inc/config.inc.php');

$result = ColonyClass::getObjectsBy("WHERE id NOT IN (SELECT object_id FROM stu_database_entrys WHERE category_id=5)");
foreach ($result as $key => $obj) {
	$db = new DatabaseEntryData;
	$db->setCategoryId(5);
	$db->setDescription($obj->getName());
	$db->setSort($obj->getId());
	$db->setObjectId($obj->getId());
	$db->save();
	$obj->setDatabaseId($db->getId());
	$obj->save();
}
?>
