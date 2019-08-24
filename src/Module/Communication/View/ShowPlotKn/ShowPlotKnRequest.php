<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPlotKn;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowPlotKnRequest implements ShowPlotKnRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }

    public function getKnOffset(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }
}