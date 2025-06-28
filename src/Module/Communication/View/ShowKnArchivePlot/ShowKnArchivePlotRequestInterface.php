<?php

namespace Stu\Module\Communication\View\ShowKnArchivePlot;

interface ShowKnArchivePlotRequestInterface
{
    public function getPlotId(): int;

    public function getKnOffset(): int;
}
