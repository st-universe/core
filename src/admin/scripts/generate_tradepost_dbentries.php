<?php

use Psr\Container\ContainerInterface;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Config\Init;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $repository = $dic->get(DatabaseEntryRepositoryInterface::class);
    $type = $dic->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_POI);
    $category = $dic->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_TRADEPOST);
    $shipRepo = $dic->get(ShipRepositoryInterface::class);

    $result = $shipRepo->getTradePostsWithoutDatabaseEntry();
    foreach ($result as $obj) {
        $db = $repository->prototype();
        $db->setCategory($category);
        $db->setDescription($obj->getName());
        $db->setData('');
        $db->setTypeObject($type);
        $db->setSort($obj->getId());
        $db->setObjectId($obj->getId());

        $repository->save($db);

        $obj->setDatabaseEntry($db);

        $shipRepo->save($obj);
    }
});
