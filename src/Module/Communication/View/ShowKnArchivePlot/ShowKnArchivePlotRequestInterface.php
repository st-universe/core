<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlot;

interface ShowKnArchivePlotRequestInterface
{
    public function getPlotId(): int;

    public function getKnOffset(): int;
}
