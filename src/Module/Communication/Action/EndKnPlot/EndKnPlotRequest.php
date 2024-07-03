<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EndKnPlot;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EndKnPlotRequest implements EndKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }
}
