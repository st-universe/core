<?php

use Stu\Lib\DbInterface;
use Stu\Module\Tick\Process\ProcessTickInterface;

require_once __DIR__.'/../../inc/config.inc.php';

/**
 * @var ProcessTickInterface[] $handlerList
 */
$handlerList = $container->get('process_tick_handler');

$db = $container->get(DbInterface::class);

$db->beginTransaction();

foreach ($handlerList as $process) {
    $process->work();
}

$db->commitTransaction();
