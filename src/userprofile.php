<?php

use Stu\Control\UserProfileController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(UserProfileController::class);

DB()->commitTransaction();
