<?php

use Stu\Control\GameControllerInterface;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('MAINDESK_ACTIONS'),
    $container->get('MAINDESK_VIEWS')
);

DB()->commitTransaction();
