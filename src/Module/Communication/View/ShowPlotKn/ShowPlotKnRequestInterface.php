<?php

namespace Stu\Module\Communication\View\ShowPlotKn;

interface ShowPlotKnRequestInterface
{
    public function getPlotId(): int;

    public function getKnOffset(): int;
}