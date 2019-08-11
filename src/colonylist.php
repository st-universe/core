<?php

use Stu\Control\ColonyListController;

@session_start();
require_once 'inc/config.inc.php';

DB()->beginTransaction();

$container->get(ColonyListController::class)->main();

DB()->commitTransaction();
