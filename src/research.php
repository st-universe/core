<?php

use Stu\Control\ResearchController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(ResearchController::class);

DB()->commitTransaction();
