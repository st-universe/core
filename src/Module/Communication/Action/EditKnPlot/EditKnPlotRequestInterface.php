<?php

namespace Stu\Module\Communication\Action\EditKnPlot;

interface EditKnPlotRequestInterface
{
    public function getPlotId(): int;

    public function getText(): string;

    public function getTitle(): string;
}
