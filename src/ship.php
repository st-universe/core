<?php

use Stu\Control\ShipController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(ShipController::class);

DB()->commitTransaction();
