<?php

use Stu\Control\DatabaseController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(DatabaseController::class);

DB()->commitTransaction();
