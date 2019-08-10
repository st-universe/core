<?php

use Stu\Control\AdminController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(AdminController::class);

DB()->commitTransaction();
