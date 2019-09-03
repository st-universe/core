<?php

use Stu\Lib\UserDeletion;

include_once(__DIR__.'/../../inc/config.inc.php');

DB()->beginTransaction();

UserDeletion::handleReset();

$user = new UserData(array());
$user->forceId(101);
$user->setLogin('wolverine');
$user->setUser('Wolverine');
$user->setFaction(FACTION_FEDERATION);
$user->setActive(User::USER_ACTIVE);
$user->save();

DB()->query('ALTER TABLE stu_user AUTO_INCREMENT=101');


DB()->commitTransaction();