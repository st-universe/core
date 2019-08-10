<?php

use Stu\Control\IndexController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(IndexController::class);

DB()->commitTransaction();
