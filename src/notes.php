<?php

use Stu\Control\NotesController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$controller = $container->get(NotesController::class);

DB()->commitTransaction();
