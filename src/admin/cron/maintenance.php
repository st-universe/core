<?php

use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;

require_once __DIR__ . '/../../Config/Bootstrap.php';

/**
 * @deprecated Use cli/new cron system
 */

/** @var ContainerInterface $container */
$container->get(MaintenanceTickRunner::class)->run();
