<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Stu\TestUser;
use Stu\TwigTestCase;

class ShowRegionInfoFest extends TwigTestCase
{
    protected function getViewController(): string
    {
        return ShowRegionInfo::class;
    }

    public function testHandle(): void
    {
        $userId = $this->loadTestData(new TestUser());

        $this->renderSnapshot();
    }
}
