<?php

use Stu\Control\OptionsController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(OptionsController::class);

DB()->commitTransaction();
