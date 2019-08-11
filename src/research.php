<?php

use Stu\Control\ResearchController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(ResearchController::class)->main();

DB()->commitTransaction();
