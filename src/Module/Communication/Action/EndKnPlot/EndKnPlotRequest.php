<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EndKnPlotRequest implements EndKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }
}