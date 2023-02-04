<?php

use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use Stu\Config\Init;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    /**
     * @todo Remove $container after magic dic calls have been purged
     */
    global $container;
    $container = $dic;

    $entityManager = $dic->get(EntityManagerInterface::class);
    $config = $dic->get(ConfigInterface::class);
    $resetManager = $dic->get(ResetManagerInterface::class);

    $entityManager->beginTransaction();

    try {
        $resetManager->performReset();
    } catch (Throwable $t) {
        $entityManager->rollback();

        throw $t;
    }

    $entityManager->getConnection()->executeQuery(
        sprintf(
            'ALTER SEQUENCE stu_user_id_seq RESTART WITH %d',
            (int) $config->get('game.admin.id')
        )
    );

    $entityManager->commit();
});
