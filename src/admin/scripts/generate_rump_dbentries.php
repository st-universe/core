<?php

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP);
$category = $container->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_SHIPRUMP);
$shipRumpRepo = $container->get(ShipRumpRepositoryInterface::class);

$result = $shipRumpRepo->getWithoutDatabaseEntry();
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
