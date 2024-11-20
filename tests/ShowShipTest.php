<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Override;
use request;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\TestShip;
use Stu\TestUser;
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
        $shipId = $this->loadTestData(new TestShip(2, 5, 5));

        $this->renderSnapshot(['id' => $shipId]);
    }
}
