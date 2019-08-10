<?php

use Stu\Control\StarmapController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(StarmapController::class);

DB()->commitTransaction();
