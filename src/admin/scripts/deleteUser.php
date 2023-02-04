<?php

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Config\Init;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    /**
     * @todo Remove $container after magic dic calls have been purged
     */
    global $container;
    $container = $dic;

    $entityManager = $dic->get(EntityManagerInterface::class);
    $playerDeletion = $dic->get(PlayerDeletionInterface::class);

    $entityManager->beginTransaction();

    try {
        $playerDeletion->handleDeleteable();

        $entityManager->commit();
    } catch (Throwable $t) {
        $entityManager->rollback();

        throw $t;
    }
});
