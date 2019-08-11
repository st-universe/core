<?php

use Stu\Control\UserProfileController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(UserProfileController::class)->main();

DB()->commitTransaction();
