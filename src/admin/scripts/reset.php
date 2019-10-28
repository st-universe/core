<?php

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);
$userRepo = $container->get(UserRepositoryInterface::class);
$factionRepo = $container->get(FactionRepositoryInterface::class);
$config = $container->get(ConfigInterface::class);
$playerDeletion = $container->get(PlayerDeletionInterface::class);
$playerCreator = $container->get(PlayerCreatorInterface::class);

$entityManager->beginTransaction();

try {
    $playerDeletion->handleReset();
} catch (Throwable $t) {
    $entityManager->rollback();

    throw $t;
}

$entityManager->getConnection()->query(
    sprintf(
        'ALTER TABLE stu_user AUTO_INCREMENT = %d',
        $config->get('game.admin.id')
    )
);

$playerCreator->createPlayer(
    $config->get('game.admin.username'),
    $config->get('game.admin.email'),
    $factionRepo->find($config->get('game.admin.faction'))
);
