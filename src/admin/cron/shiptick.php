<?php

use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Ship\ShipTickRunner;

require_once __DIR__ . '/../../Config/Bootstrap.php';

/**
 * @deprecated Use cli/new cron system
 */

/** @var ContainerInterface $container */
$container->get(ShipTickRunner::class)->run();
