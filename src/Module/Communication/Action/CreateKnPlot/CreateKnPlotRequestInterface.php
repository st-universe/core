<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\CreateKnPlot;

interface CreateKnPlotRequestInterface
{
    public function getText(): string;

    public function getTitle(): string;
}
