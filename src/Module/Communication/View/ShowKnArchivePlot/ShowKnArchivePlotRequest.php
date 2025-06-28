<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchivePlot;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowKnArchivePlotRequest implements ShowKnArchivePlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }

    #[Override]
    public function getKnOffset(): int
    {
        return $this->parameter('mark')->int()->defaultsTo(0);
    }
}
