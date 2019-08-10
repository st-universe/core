<?php

use Stu\Control\ColonyController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(ColonyController::class);

DB()->commitTransaction();
