<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;

interface LayerRendererInterface
{
    public function render(CellDataInterface $data, AbstractVisualPanel $panel): string;
}
