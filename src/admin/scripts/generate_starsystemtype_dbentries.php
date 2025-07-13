<?php

use Psr\Container\ContainerInterface;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Config\Init;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $starSystemTypeRepo = $dic->get(StarSystemTypeRepositoryInterface::class);

    $repository = $dic->get(DatabaseEntryRepositoryInterface::class);
    $type = $dic->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_STARSYSTEM_TYPE);
    $category = $dic->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::STAR_SYSTEM_TYPE->value);

    if ($type === null || $category === null) {
        throw new RuntimeException('type or category is null');
    }

    $result = $starSystemTypeRepo->getWithoutDatabaseEntry();
    foreach ($result as $obj) {
        $db = $repository->prototype();
        $db->setCategory($category);
        $db->setDescription($obj->getDescription());
        $db->setSort($obj->getId());
        $db->setData('');
        $db->setTypeObject($type);
        $db->setObjectId($obj->getId());

        $repository->save($db);

        $obj->setDatabaseEntry($db);
        $starSystemTypeRepo->save($obj);
    }
});
