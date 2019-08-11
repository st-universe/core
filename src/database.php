<?php

use Stu\Control\DatabaseController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(DatabaseController::class)->main();

DB()->commitTransaction();
