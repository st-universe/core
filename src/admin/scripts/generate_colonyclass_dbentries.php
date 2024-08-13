<?php

use Psr\Container\ContainerInterface;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Config\Init;
use Stu\Orm\Repository\ColonyClassRepositoryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $databaseEntryRepository = $dic->get(DatabaseEntryRepositoryInterface::class);
    $colonyClassRepository = $dic->get(ColonyClassRepositoryInterface::class);

    $type = $dic->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_PLANET);
    if ($type === null) {
        throw new RuntimeException('type %d does not exist', DatabaseEntryTypeEnum::DATABASE_TYPE_PLANET);
    }

    $category = $dic->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS);
    if ($category === null) {
        throw new RuntimeException('category %d does not exist', DatabaseCategoryTypeEnum::DATABASE_CATEGORY_COLONY_CLASS);
    }

    $result = $colonyClassRepository->getWithoutDatabaseEntry();
    foreach ($result as $obj) {
        $db = $databaseEntryRepository->prototype();
        $db->setCategory($category);
        $db->setData('');
        $db->setDescription($obj->getName());
        $db->setTypeObject($type);
        $db->setSort($obj->getId());
        $db->setObjectId($obj->getId());
        $databaseEntryRepository->save($db);

        $obj->setDatabaseEntry($db);
        $colonyClassRepository->save($obj);
    }
});
