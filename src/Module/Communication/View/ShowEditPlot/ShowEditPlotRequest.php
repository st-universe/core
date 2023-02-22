<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowEditPlotRequest implements ShowEditPlotRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }
}
