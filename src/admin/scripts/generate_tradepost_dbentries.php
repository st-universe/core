<?php

use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DATABASE_TYPE_POI);
$category = $container->get(DatabaseCategoryRepositoryInterface::class)->find(DATABASE_CATEGORY_TRADEPOST);

$result = Ship::getObjectsBy('WHERE database_id = 0 and trade_post_id > 0');
foreach ($result as $key => $obj) {
    $db = $repository->prototype();
	$db->setCategory($category);
	$db->setDescription($obj->getName());
	$db->setData('');
    $db->setTypeObject($type);
	$db->setSort($obj->getId());
	$db->setObjectId($obj->getId());

	$repository->save($db);

	$obj->setDatabaseId($db->getId());
	$obj->save();
}
