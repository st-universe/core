<?php

use Stu\Control\MaindeskController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(MaindeskController::class)->main();

DB()->commitTransaction();
