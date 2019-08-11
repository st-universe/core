<?php

use Stu\Control\StarmapController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(StarmapController::class)->main();

DB()->commitTransaction();
