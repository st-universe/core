<?php

use Stu\Module\Control\GameControllerInterface;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('COMMUNICATION_ACTIONS'),
    $container->get('COMMUNICATION_VIEWS')
);

DB()->commitTransaction();
