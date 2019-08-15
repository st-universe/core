<?php

use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

// @todo rebuild

$db_category = '00'.DATABASE_TYPE_SHIPRUMP;

$repository = $container->get(DatabaseEntryRepositoryInterface::class);

//DatabaseEntry::truncate('WHERE category_id='.$db_category);

$result = Shiprump::getBy('WHERE is_buildable=1');
foreach ($result as $key => $obj) {
    $db = $repository->prototype();
	$db->setCategoryId($db_category);
	$db->setDescription($obj->getName());
	$db->setData('');
	$db->setSort($obj->getSort());
	$db->setObjectId($obj->getId());
	$db->setType(DATABASE_TYPE_RUMP);

	$repository->save($db);

	//$db->forceId($obj->getId().$db_category);
	$obj->setDatabaseId($db->getId());
	$obj->save();
}
