<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateKnPlotRequest implements CreateKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->queryParameter('description')->string()->trim()->required()
        );
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->queryParameter('title')->string()->trim()->required()
        );
    }
}
