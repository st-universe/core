<?php

use Stu\Control\AllianceController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(AllianceController::class)->main();

DB()->commitTransaction();
