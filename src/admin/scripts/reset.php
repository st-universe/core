<?php

use Stu\Lib\UserDeletion;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

DB()->beginTransaction();

UserDeletion::handleReset();

$userRepo = $container->get(UserRepositoryInterface::class);

DB()->query('ALTER TABLE stu_user AUTO_INCREMENT=100');

$user = $userRepo->prototype();
$user->setLogin('wolverine');
$user->setUser('Wolverine');
$user->setFaction(FACTION_FEDERATION);
$user->setActive(PlayerEnum::USER_ACTIVE);

$userRepo->save();


DB()->commitTransaction();