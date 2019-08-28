<?php

use Stu\Control\GameControllerInterface;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('ALLIANCE_ACTIONS'),
    $container->get('ALLIANCE_VIEWS')
);

DB()->commitTransaction();
