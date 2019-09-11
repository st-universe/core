<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Control\GameControllerInterface;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

$em = $container->get(EntityManagerInterface::class);
$em->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('NOTES_ACTIONS'),
    $container->get('NOTES_VIEWS')
);

$em->commit();
