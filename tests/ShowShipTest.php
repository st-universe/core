<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Mockery;
use Override;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\TwigTestCase;

class ShowShipTest extends TwigTestCase
{
    #[Override]
    protected function getViewControllerClass(): string
    {
        return ShowShip::class;
    }

    public function testHandle(): void
    {
        $anomalyRepositoryMock = $this->mock(AnomalyRepositoryInterface::class);
        $anomalyRepositoryMock->shouldReceive('getClosestAnomalyDistance')
            ->with(Mockery::any())
            ->andReturn(null);

        $this->getContainer()->setAdditionalService(AnomalyRepositoryInterface::class, $anomalyRepositoryMock);

        $this->renderSnapshot(['id' => 42]);
    }
}
