<?php

use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DATABASE_TYPE_SHIPRUMP);
$category = $container->get(DatabaseCategoryRepositoryInterface::class)->find(DATABASE_CATEGORY_SHIPRUMP);

$result = Shiprump::getBy('WHERE is_buildable=1 AND id NOT IN (select object_id from stu_database_entrys where type='.DATABASE_TYPE_SHIPRUMP.')');
foreach ($result as $key => $obj) {
    $db = $repository->prototype();
	$db->setCategory($category);
	$db->setDescription($obj->getName());
	$db->setData('');
	$db->setSort($obj->getSort());
	$db->setObjectId($obj->getId());
	$db->setTypeObject($type);

	$repository->save($db);

	$obj->setDatabaseId($db->getId());
	$obj->save();
}
