<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\Layer;

final class MapLayerRenderer implements LayerRendererInterface
{
    public function __construct(private Layer $layer, private EncodedMapInterface $encodedMap) {}

    /** @param MapData $data */
    #[\Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        return sprintf(
            '<img src="/assets/map/%s" style="z-index: %d; %s opacity:1;" />',
            $this->getMapGraphicPath($data),
            PanelLayerEnum::MAP->value,
            $panel->getHeightAndWidth()
        );
    }

    private function getMapGraphicPath(MapData $data): string
    {
        $layer = $this->layer;
        if ($layer->isEncoded()) {

            return $this->encodedMap->getEncodedMapPath(
                $data->getMapfieldType(),
                $layer
            );
        }

        return sprintf('%d/%d.png', $layer->getId(), $data->getMapfieldType());
    }
}
