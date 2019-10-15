<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);
$playerDeletion = $container->get(PlayerDeletionInterface::class);

$entityManager->beginTransaction();

try {
    $playerDeletion->handleDeleteable();

    $entityManager->commit();
} catch (Throwable $t) {
    $entityManager->rollback();

    throw $t;
}
