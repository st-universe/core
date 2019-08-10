<?php

use Stu\Control\AllianceController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(AllianceController::class);

DB()->commitTransaction();
