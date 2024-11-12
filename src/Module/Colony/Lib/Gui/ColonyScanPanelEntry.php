<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Lib\Map\VisualPanel\VisualPanelElementInterface;

class ColonyScanPanelEntry implements VisualPanelElementInterface
{
    private string $cssClass = 'lss';

    public function __construct(protected int $x, protected int $y, protected PanelLayers $layers) {}

    /** @return array<string> */
    public function getRenderedCellLayers(): array
    {
        return $this->layers->getRenderedCellLayers($this->x, $this->y);
    }

    public function getBorder(): string
    {
        return $this->layers->getCellBorder($this->x, $this->y);
    }

    public function getCssClass(): string
    {
        return $this->cssClass;
    }
}
