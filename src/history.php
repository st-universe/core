<?php

use Stu\Control\IntermediateController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(IntermediateController::TYPE_HISTORY)->main();

DB()->commitTransaction();
