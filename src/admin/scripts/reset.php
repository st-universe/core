<?php

use Stu\Module\Maintenance\IdleUserDeletion;

include_once(__DIR__.'/../../inc/config.inc.php');

DB()->beginTransaction();

IdleUserDeletion::handleReset();
User::createAdminUsers();

DB()->commitTransaction();