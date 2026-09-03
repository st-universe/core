<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

interface ShowKnPlotRequestInterface
{
    public function getPlotId(): int;

    public function getKnOffset(): int;
}
