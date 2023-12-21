<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\ColonyShieldData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;

final class ColonyShieldLayerRenderer implements LayerRendererInterface
{
    /** @param ColonyShieldData $data */
    public function render(CellDataInterface $data, AbstractVisualPanel $panel): string
    {
        if (!$data->getShieldState()) {
            return '';
        }

        return sprintf(
            '<img src="/assets/planets/s1.png" class="lssColoShield" style="z-index: %d; %s" />',
            PanelLayerEnum::COLONY_SHIELD->value,
            $panel->getHeightAndWidth()
        );
    }
}
