<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\AnomalyData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

final class AnomalyLayerRenderer implements LayerRendererInterface
{
    /** @param AnomalyData $data */
    #[Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        if ($data->getAnomalyTypes() === null) {
            return '';
        }

        $zIndex = PanelLayerEnum::ANOMALIES->value;

        return implode(
            "\n",
            array_map(function (string $anomalyType) use (&$zIndex, $panel): string {
                return sprintf(
                    '<img src="/assets/map/anomalies/%s.png" class="visualPanelLayer" style="z-index: %d; %s opacity: 0.8;" />',
                    $anomalyType,
                    $zIndex++,
                    $panel->getHeightAndWidth()
                );
            }, $data->getAnomalyTypes())
        );
    }
}
