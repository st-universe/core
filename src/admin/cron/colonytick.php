<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;

require_once __DIR__.'/../../inc/config.inc.php';

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

$tickManager = $container->get(ColonyTickManagerInterface::class);
$tickManager->work(1);

$db->commit();
