<?php

use Stu\Module\Tick\Maintenance\Maintenance;

require_once __DIR__.'/../../inc/config.inc.php';

$maintenance = new Maintenance(
    $container->get('maintenance_handler')
);
$maintenance->handle();
