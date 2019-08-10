<?php

use Stu\Control\HistoryController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(HistoryController::class);

DB()->commitTransaction();
