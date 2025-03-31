<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\ColonyShieldData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

final class ColonyShieldLayerRenderer implements LayerRendererInterface
{
    /** @param ColonyShieldData $data */
    #[Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        if (!$data->isShielded()) {
            return '';
        }

        return sprintf(
            '<img src="/assets/planets/s1.png" class="lssColoShield" style="z-index: %d; %s" />',
            PanelLayerEnum::COLONY_SHIELD->value,
            $panel->getHeightAndWidth()
        );
    }
}
