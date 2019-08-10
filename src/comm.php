<?php

use Stu\Control\CommController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(CommController::class);

DB()->commitTransaction();
