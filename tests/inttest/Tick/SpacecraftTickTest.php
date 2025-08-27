<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;

class SpacecraftTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        Init::getContainer()
            ->get(SpacecraftTickManagerInterface::class)
            ->work(false);
    }
}
