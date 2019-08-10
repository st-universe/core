<?php

use Stu\Control\TradeController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(TradeController::class);

DB()->commitTransaction();
