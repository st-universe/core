<?php

require_once __DIR__ . '/../src/Config/Bootstrap.php';

use Stu\Component\Queue\Consumer\DelayedBuildingJobConsumerInterface;

$consumer = $container->get(DelayedBuildingJobConsumerInterface::class);

$consumer->consume();
