<?php

use Stu\Control\GameControllerInterface;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('INDEX_ACTIONS'),
    $container->get('INDEX_VIEWS'),
    false
);

DB()->commitTransaction();
