<?php
include_once(__DIR__.'/../../inc/config.inc.php');

$db_category = '00'.DATABASE_TYPE_SHIPRUMP;

DatabaseEntry::truncate('WHERE category_id='.$db_category);

$result = Shiprump::getBy('WHERE is_buildable=1');
foreach ($result as $key => $obj) {
	$db = new DatabaseEntryData;
	$db->setCategoryId($db_category);
	$db->setDescription($obj->getName());
	$db->setSort($obj->getSort());
	$db->setObjectId($obj->getId());
	$db->setType(DATABASE_TYPE_RUMP);
	$db->save();
	$db->forceId($obj->getId().$db_category);
	$db->save();
	$obj->setDatabaseId($db->getId());
	$obj->save();
}
