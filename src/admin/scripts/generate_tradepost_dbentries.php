<?php

use Psr\Container\ContainerInterface;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Config\Init;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $repository = $dic->get(DatabaseEntryRepositoryInterface::class);
    $type = $dic->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_POI);
    $category = $dic->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST);
    $stationRepo = $dic->get(StationRepositoryInterface::class);

    if ($type === null || $category === null) {
        throw new RuntimeException('type or category is null');
    }

    $result = $stationRepo->getTradePostsWithoutDatabaseEntry();
    foreach ($result as $obj) {
        $db = $repository->prototype();
        $db->setCategory($category);
        $db->setDescription($obj->getName());
        $db->setData('');
        $db->setTypeObject($type);
        $db->setSort($obj->getId());
        $db->setObjectId($obj->getId());

        $repository->save($db);

        $obj->setDatabaseId($db->getId());

        $stationRepo->save($obj);
    }
});
