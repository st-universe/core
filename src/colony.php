<?php

use Stu\Control\ColonyController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(ColonyController::class)->main();

DB()->commitTransaction();
