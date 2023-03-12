<?php

namespace Stu\Module\Communication\Action\CreateKnPlot;

interface CreateKnPlotRequestInterface
{
    public function getText(): string;

    public function getTitle(): string;
}