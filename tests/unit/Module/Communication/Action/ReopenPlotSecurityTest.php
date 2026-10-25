<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action;

use Stu\Module\Communication\Action\ReopenKnPlot\ReopenKnPlot;
use Stu\Module\Communication\Action\ReopenKnPlot\ReopenKnPlotRequestInterface;
use Stu\Module\Communication\Lib\PlotMemberServiceInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\ProtectedMutationTestCase;

final class ReopenPlotSecurityTest extends ProtectedMutationTestCase
{
    public function testReopenRejectsForgedRequests(): void
    {
        $this->assertRequestProtection(new ReopenKnPlot(
            $this->mock(ReopenKnPlotRequestInterface::class),
            $this->mock(RpgPlotRepositoryInterface::class),
            $this->mock(PlotMemberServiceInterface::class),
            $this->mock(StuTime::class)
        ));
    }
}
