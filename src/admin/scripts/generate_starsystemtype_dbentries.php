<?php

use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DATABASE_TYPE_STARSYSTEM_TYPE);

$result = SystemType::getObjectsBy('WHERE id NOT IN (SELECT object_id FROM stu_database_entrys WHERE category_id=6)');
foreach ($result as $key => $obj) {
    $db = $repository->prototype();
	$db->setCategoryId(6);
	$db->setDescription($obj->getDescription());
	$db->setSort($obj->getId());
	$db->setData('');
	$db->setTypeObject($type);
	$db->setObjectId($obj->getId());

	$repository->save($db);

	$obj->setDatabaseId($db->getId());
	$obj->save();
}
