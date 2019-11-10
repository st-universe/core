<?php

namespace Stu\Module\Communication\View\ShowKnPlot;

interface ShowKnPlotRequestInterface
{
    public function getPlotId(): int;

    public function getKnOffset(): int;
}
