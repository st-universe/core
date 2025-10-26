<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateKnPlotRequest implements CreateKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('description')->string()->trim()->required()
        );
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->parameter('title')->string()->trim()->required()
        );
    }
}
