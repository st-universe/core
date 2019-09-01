<?php

use Stu\Module\Tick\TickManagerInterface;

require_once __DIR__.'/../../inc/config.inc.php';

$container->get(TickManagerInterface::class)->work();
