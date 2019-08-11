<?php

use Stu\Control\ShiplistController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(ShiplistController::class)->main();

DB()->commitTransaction();
