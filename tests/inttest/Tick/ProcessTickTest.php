<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;

class ProcessTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        $handlerList = Init::getContainer()
            ->get(ProcessTickHandlerInterface::class);

        foreach ($handlerList as $process) {
            $process->work();
        }
    }
}
