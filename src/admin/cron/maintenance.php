<?php
require_once __DIR__.'/../../inc/config.inc.php';

$maintenance = new Maintenance(
    $container->get('maintenance_handler')
);
$maintenance->handle();
