<?php

use Stu\Control\ShiplistController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(ShiplistController::class);

DB()->commitTransaction();
