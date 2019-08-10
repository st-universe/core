<?php

use Stu\Control\CrewController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(CrewController::class);

DB()->commitTransaction();
