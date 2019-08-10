<?php

use Stu\Control\LogoutController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(LogoutController::class);
$controller->logout();

DB()->commitTransaction();
