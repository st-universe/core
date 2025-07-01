<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\LssBlockade\LssBlockadeGrid;

class PanelLayers
{
    /** @var Collection<int, PanelLayer> */
    private Collection $layers;

    private ?PanelLayer $borderLayer = null;

    public function __construct(
        private readonly AbstractVisualPanel $panel,
        private readonly ?LssBlockadeGrid $lssBlockadeGrid
    ) {
        $this->layers = new ArrayCollection();
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
        return $this->layers
            ->filter(fn(PanelLayer $layer, int $type): bool => $this->isLayerVisible($type, $x, $y))
            ->map(fn(PanelLayer $layer): string => $layer->renderCell($x, $y, $this->panel))
            ->toArray();
    }

    private function isLayerVisible(int $type, int $x, int $y): bool
    {
        if ($this->lssBlockadeGrid === null) {
            return true;
        }

        return !PanelLayerEnum::from($type)->isAffectedByLssBlockade()
            || $this->lssBlockadeGrid->isVisible($x, $y);
    }

    public function getCellBorder(int $x, int $y): string
    {
        if ($this->borderLayer === null) {
            return '';
        }

        return $this->borderLayer->renderCell($x, $y, $this->panel);
    }
}
