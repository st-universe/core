<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

$colonyCorrector  = $container->get(ColonyCorrectorInterface::class);

try {
    $colonyCorrector->correct();

    $entityManager->commit();
} catch (Throwable $t) {
    $entityManager->rollback();

    throw $t;
}
