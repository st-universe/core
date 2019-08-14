<?php

use Stu\Lib\UserDeletion;

include_once(__DIR__.'/../../inc/config.inc.php');

DB()->beginTransaction();

UserDeletion::handleReset();
User::createAdminUsers();

DB()->commitTransaction();