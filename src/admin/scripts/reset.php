<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Lib\UserDeletion;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

UserDeletion::handleReset();

$userRepo = $container->get(UserRepositoryInterface::class);
$factionRepo = $container->get(FactionRepositoryInterface::class);

$entityManager->getConnection()->query(
    'ALTER TABLE stu_user AUTO_INCREMENT=101'
);

$playerCreator = $container->get(PlayerCreatorInterface::class);

$playerCreator->create(
    'wolverine',
    'stu@usox.org',
    $factionRepo->find(FactionEnum::FACTION_FEDERATION)
);


$entityManager->commit();
