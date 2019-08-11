<?php

use Stu\Control\OptionsController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(OptionsController::class)->main();

DB()->commitTransaction();
