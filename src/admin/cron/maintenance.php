<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tick\Maintenance\Maintenance;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

require_once __DIR__.'/../../inc/config.inc.php';

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

$maintenance = new Maintenance(
    $container->get(GameConfigRepositoryInterface::class),
    $container->get('maintenance_handler')
);
$maintenance->handle();

$entityManager->commit();
