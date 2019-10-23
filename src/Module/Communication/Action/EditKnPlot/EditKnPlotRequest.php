<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditKnPlotRequest implements EditKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }

    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('description')->string()->trim()->required()
        );
    }

    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->required()
        );
    }
}
