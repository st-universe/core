<?php

use Stu\Control\CrewController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(CrewController::class)->main();

DB()->commitTransaction();
