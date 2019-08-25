<?php

use Stu\Control\IntermediateController;

@session_start();
require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(IntermediateController::TYPE_COLONY_LIST)->main();

DB()->commitTransaction();
