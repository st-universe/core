<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Mockery;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\TwigTestCase;

class ShowShipTest extends TwigTestCase
{
    public function testHandle(): void
    {
        $anomalyRepositoryMock = $this->mock(AnomalyRepositoryInterface::class);
        $anomalyRepositoryMock->shouldReceive('getClosestAnomalyDistance')
            ->with(Mockery::any())
            ->andReturn(null);

        $this->getContainer()->setAdditionalService(AnomalyRepositoryInterface::class, $anomalyRepositoryMock);

        $this->renderSnapshot(
            101,
            ShowSpacecraft::class,
            ['id' => 42]
        );
    }
}
