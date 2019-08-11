<?php

use Stu\Control\ShipController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(ShipController::class)->main();

DB()->commitTransaction();
