<?php

use Stu\Control\IndexController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(IndexController::class)->main(false);

DB()->commitTransaction();
