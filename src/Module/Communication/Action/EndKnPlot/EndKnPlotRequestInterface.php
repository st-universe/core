<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

interface EndKnPlotRequestInterface
{
    public function getPlotId(): int;
}
