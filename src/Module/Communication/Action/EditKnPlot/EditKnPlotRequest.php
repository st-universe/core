<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditKnPlot;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditKnPlotRequest implements EditKnPlotRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }

    #[Override]
    public function getText(): string
    {
        return $this->tidyString(
            $this->parameter('description')->string()->trim()->required()
        );
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->tidyString(
            $this->parameter('title')->string()->trim()->required()
        );
    }
}
