<?php

declare(strict_types=1);

namespace Stu\Tick;

use Mockery;
use Stu\ActionTestCase;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

class SpacecraftTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        $dic = $this->getContainer();

        $colonyshipRepairRepoMock = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $colonyshipRepairRepoMock->shouldReceive('getMostRecentJobs')
            ->with(Mockery::any())
            ->andReturn([]);
        $dic->setAdditionalService(ColonyShipRepairRepositoryInterface::class, $colonyshipRepairRepoMock);

        $dic->get(SpacecraftTickManagerInterface::class)
            ->work(false);

        $this->assertEntity(42, Spacecraft::class, ['crewCount' => 0]);
    }
}
