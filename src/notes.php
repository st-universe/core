<?php

use Stu\Control\NotesController;

@session_start();

require_once __DIR__.'/inc/config.inc.php';

DB()->beginTransaction();

$container->get(NotesController::class)->main();

DB()->commitTransaction();
