<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Control\GameControllerInterface;

@session_start();
require_once __DIR__ . '/../Config/Bootstrap.php';

$em = $container->get(EntityManagerInterface::class);
$em->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    'trade',
    $container->get('TRADE_ACTIONS'),
    $container->get('TRADE_VIEWS')
);

$em->commit();