<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Control\GameControllerInterface;

@session_start();

require_once __DIR__ . '/../Config/Bootstrap.php';

$em = $container->get(EntityManagerInterface::class);
$em->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    'ship',
    array_merge($container->get('SHIP_ACTIONS'), $container->get('STATION_ACTIONS')),
    $container->get('SHIP_VIEWS')
);

$em->commit();
