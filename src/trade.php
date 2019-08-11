<?php

use Stu\Control\TradeController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(TradeController::class)->main();

DB()->commitTransaction();
