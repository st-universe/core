<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ReopenKnPlot;

interface ReopenKnPlotRequestInterface
{
    public function getPlotId(): int;
}
