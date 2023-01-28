<?php

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);
$userRepo = $container->get(UserRepositoryInterface::class);
$factionRepo = $container->get(FactionRepositoryInterface::class);
$config = $container->get(ConfigInterface::class);
$playerCreator = $container->get(PlayerCreatorInterface::class);
$resetManager = $container->get(ResetManagerInterface::class);

$entityManager->beginTransaction();

try {
    $resetManager->performReset();
} catch (Throwable $t) {
    $entityManager->rollback();

    throw $t;
}

$entityManager->getConnection()->query(
    sprintf(
        'ALTER SEQUENCE stu_user_id_seq RESTART WITH %d',
        $config->get('game.admin.id')
    )
);

$entityManager->commit();
