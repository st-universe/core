<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\ReopenKnPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ReopenKnPlotRequest implements ReopenKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }
}
