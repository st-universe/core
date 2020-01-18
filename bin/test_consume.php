<?php

require_once __DIR__ . '/../src/Config/Bootstrap.php';

use Stu\Component\Queue\Consumer\DelayedJobConsumerInterface;

$consumer = $container->get(DelayedJobConsumerInterface::class);

$consumer->consume();
