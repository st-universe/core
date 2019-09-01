<?php

use Stu\Lib\DbInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;

require_once __DIR__.'/../../inc/config.inc.php';

$db = $container->get(DbInterface::class);

$db->beginTransaction();

$container->get(ShipTickManagerInterface::class)->work();

$db->commitTransaction();
