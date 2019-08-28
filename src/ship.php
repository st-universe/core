<?php

use Stu\Module\Control\GameControllerInterface;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('SHIP_ACTIONS'),
    $container->get('SHIP_VIEWS')
);

DB()->commitTransaction();
