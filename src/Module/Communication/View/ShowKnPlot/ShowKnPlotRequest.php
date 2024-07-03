<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnPlot;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnPlotRequest implements ShowKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }

    #[Override]
    public function getKnOffset(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }
}
