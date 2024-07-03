<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Render\BorderLayerRenderer;

class PanelLayers
{
    /** @var array<int, PanelLayer> */
    private array $layers = [];

    private ?PanelLayer $borderLayer = null;

    public function __construct(private AbstractVisualPanel $panel)
    {
    }

    public function addLayer(PanelLayerEnum $type, PanelLayer $layer): void
    {
        if ($type === PanelLayerEnum::BORDER) {
            $this->borderLayer = $layer;
        } else {
            $this->layers[$type->value] = $layer;
        }
    }

    /** @return array<string> */
    public function getRenderedCellLayers(int $x, int $y): array
    {
        return array_map(
            fn (PanelLayer $layer): string => $layer->renderCell($x, $y, $this->panel),
            $this->layers
        );
    }

    public function getCellBorder(int $x, int $y): string
    {
        if ($this->borderLayer === null) {
            return BorderLayerRenderer::DEFAULT_BORDER_COLOR;
        }

        $rendered = $this->borderLayer->renderCell($x, $y, $this->panel);
        if ($rendered === '') {
            return BorderLayerRenderer::DEFAULT_BORDER_COLOR;
        }

        return $rendered;
    }
}
