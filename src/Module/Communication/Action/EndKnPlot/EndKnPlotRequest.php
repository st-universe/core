<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EndKnPlotRequest implements EndKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }
}
