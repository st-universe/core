<?php

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$starSystemTypeRepo = $container->get(StarSystemTypeRepositoryInterface::class);

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_STARSYSTEM_TYPE);
$category = $container->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STAR_SYSTEM_TYPE);

$result = $starSystemTypeRepo->getWithoutDatabaseEntry();
foreach ($result as $key => $obj) {
    $db = $repository->prototype();
	$db->setCategory($category);
	$db->setDescription($obj->getDescription());
	$db->setSort($obj->getId());
	$db->setData('');
	$db->setTypeObject($type);
	$db->setObjectId($obj->getId());

	$repository->save($db);

	$obj->setDatabaseId($db->getId());
	$obj->save();
}
