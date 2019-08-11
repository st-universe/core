<?php

use Stu\Control\AdminController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(AdminController::class)->main();

DB()->commitTransaction();
