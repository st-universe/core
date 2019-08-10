<?php

use Stu\Control\ColonyListController;

@session_start();
require_once 'inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(ColonyListController::class);

DB()->commitTransaction();
