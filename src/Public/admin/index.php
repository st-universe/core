<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Control\GameControllerInterface;

@session_start();

require_once __DIR__ . '/../../Config/Bootstrap.php';

$em = $container->get(EntityManagerInterface::class);
$em->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    'admin',
    $container->get('ADMIN_ACTIONS'),
    $container->get('ADMIN_VIEWS'),
    true,
    true
);

$em->commit();
