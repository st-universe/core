<?php

use Stu\Control\ControllerTypeEnum;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(ControllerTypeEnum::TYPE_MAINDESK)->main();

DB()->commitTransaction();
