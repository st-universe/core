<?php

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;

require_once __DIR__ . '/../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $entityManager = $dic->get(EntityManagerInterface::class);
    $colonyCorrector  = $dic->get(ColonyCorrectorInterface::class);

    $entityManager->beginTransaction();

    try {
        $colonyCorrector->correct();

        $entityManager->commit();
    } catch (Throwable $t) {
        $entityManager->rollback();

        throw $t;
    }
});
