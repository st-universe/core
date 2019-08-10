<?php

use Stu\Control\MaindeskController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(MaindeskController::class);

DB()->commitTransaction();
