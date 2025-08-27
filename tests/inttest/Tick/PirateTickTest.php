<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Pirate\PirateTickInterface;

class PirateTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        Init::getContainer()
            ->get(PirateTickInterface::class)
            ->work();
    }
}
