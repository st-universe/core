<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Mockery;
use Override;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\TwigTestCase;

class ShowStationTest extends TwigTestCase
{
    #[Override]
    protected function getViewControllerClass(): string
    {
        return ShowSpacecraft::class;
    }

    public function testHandle(): void
    {
        $anomalyRepositoryMock = $this->mock(AnomalyRepositoryInterface::class);
        $anomalyRepositoryMock->shouldReceive('getClosestAnomalyDistance')
            ->with(Mockery::any())
            ->andReturn(null);

        $this->getContainer()->setAdditionalService(AnomalyRepositoryInterface::class, $anomalyRepositoryMock);

        $this->renderSnapshot(101, ['id' => 43]);
    }
}
