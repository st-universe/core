<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Lib\UserDeletion;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

include_once(__DIR__.'/../../inc/config.inc.php');

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

UserDeletion::handleReset();

$userRepo = $container->get(UserRepositoryInterface::class);
$factionRepo = $container->get(FactionRepositoryInterface::class);

$entityManager->getConnection()->query(
    'ALTER TABLE stu_user AUTO_INCREMENT=100'
);

$user = $userRepo->prototype();
$user->setLogin('wolverine');
$user->setUser('Wolverine');
$user->setFaction($factionRepo->find(FACTION_FEDERATION));
$user->setActive(PlayerEnum::USER_ACTIVE);

$userRepo->save($user);

$entityManager->commit();
