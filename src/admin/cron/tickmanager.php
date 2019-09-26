<?php

use Stu\Module\Tick\TickManagerInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$container->get(TickManagerInterface::class)->work();
