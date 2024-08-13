<?php

use Psr\Container\ContainerInterface;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Config\Init;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseTypeRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $repository = $dic->get(DatabaseEntryRepositoryInterface::class);
    $type = $dic->get(DatabaseTypeRepositoryInterface::class)->find(DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP);
    $category = $dic->get(DatabaseCategoryRepositoryInterface::class)->find(DatabaseCategoryTypeEnum::DATABASE_CATEGORY_SHIPRUMP);
    $shipRumpRepo = $dic->get(ShipRumpRepositoryInterface::class);

    $result = $shipRumpRepo->getWithoutDatabaseEntry();
    foreach ($result as $obj) {
        $db = $repository->prototype();
        $db->setCategory($category);
        $db->setDescription($obj->getName());
        $db->setData('');
        $db->setSort($obj->getSort());
        $db->setObjectId($obj->getId());
        $db->setTypeObject($type);

        $repository->save($db);

        $obj->setDatabaseEntry($db);
        $shipRumpRepo->save($obj);
    }
});
