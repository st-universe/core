<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

interface LayerRendererInterface
{
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string;
}
