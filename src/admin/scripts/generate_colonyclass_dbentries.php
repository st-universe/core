<?php

use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\DatabaseTypeInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$repository = $container->get(DatabaseEntryRepositoryInterface::class);
/** @var DatabaseTypeInterface $type */
$type = $container->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_PLANET);
/** @var DatabaseCategoryInterface $category */
$category = $container->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS);

$result = $container->get(ColonyClassRepositoryInterface::class)->getWithoutDatabaseEntry();
foreach ($result as $obj) {
	$db = $repository->prototype();
	$db->setCategory($category);
	$db->setData('');
	$db->setDescription($obj->getName());
	$db->setTypeObject($type);
	$db->setSort($obj->getId());
	$db->setObjectId($obj->getId());
	$repository->save($db);

	$obj->setDatabaseId($db->getId());
	$obj->save();
}
