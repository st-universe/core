<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;

class ColonyTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        Init::getContainer()
            ->get(ColonyTickManagerInterface::class)
            ->work(1, 1);
    }
}
