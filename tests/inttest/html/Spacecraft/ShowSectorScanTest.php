<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Stu\Module\Spacecraft\View\ShowSectorScan\ShowSectorScan;
use Stu\TwigTestCase;

class ShowSectorScanTest extends TwigTestCase
{
    public function testHandle(): void
    {
        $this->renderSnapshot(
            10,
            ShowSectorScan::class,
            ['id' => 79]
        );
    }
}
