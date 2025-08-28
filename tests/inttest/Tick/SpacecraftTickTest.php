<?php

declare(strict_types=1);

namespace Stu\Tick;

use Mockery;
use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

class SpacecraftTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        $colonyshipRepairRepoMock = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $colonyshipRepairRepoMock->shouldReceive('getMostRecentJobs')
            ->with(Mockery::any())
            ->andReturn([]);
        $this->getContainer()->setAdditionalService(ColonyShipRepairRepositoryInterface::class, $colonyshipRepairRepoMock);

        Init::getContainer()
            ->get(SpacecraftTickManagerInterface::class)
            ->work(false);
    }
}
