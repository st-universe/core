<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Manager\TickManagerRunner;
use Stu\Orm\Entity\GameTurn;

class ManagerTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        Init::getContainer()
            ->get(TickManagerRunner::class)
            ->run(1, 1);

        $this->assertEntities(GameTurn::class, [
            [
                'turn' => 1,
                'startdate' => 1731247445,
                'enddate' => 1732214228,
                'pirate_fleets' => 0
            ],
            [
                'turn' => 2,
                'startdate' => 1732214228,
                'enddate' => 0,
                'pirate_fleets' => 0
            ]
        ]);
    }
}
