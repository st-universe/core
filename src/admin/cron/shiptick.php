<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;

require_once __DIR__.'/../../inc/config.inc.php';

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

$container->get(ShipTickManagerInterface::class)->work();

$db->commit();
