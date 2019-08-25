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
        return tidyString(strip_tags(
            $this->queryParameter('description')->string()->trim()->required()
        ));
    }

    public function getTitle(): string
    {
        return tidyString(strip_tags(
            $this->queryParameter('title')->string()->trim()->required()
        ));
    }
}