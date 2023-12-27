<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

final class SystemLayerRenderer implements LayerRendererInterface
{
    private const ONLY_BACKGROUND_IMAGE = 1;

    /** @param MapData $data */
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        return sprintf(
            '%s%s',
            $this->getBackgroundImage($data, $panel),
            $this->getFieldImage($data, $panel)
        );
    }

    private function getBackgroundImage(MapData $data, PanelAttributesInterface $panel): string
    {
        return sprintf(
            '<img src="/assets/map/starmap/%s.png"
                        style="z-index: %d; %s opacity:1;" />',
            $this->getSystemBackgroundId($data),
            PanelLayerEnum::BACKGROUND->value,
            $panel->getHeightAndWidth()
        );
    }

    private function getFieldImage(MapData $data, PanelAttributesInterface $panel): string
    {
        $fieldId = $this->getSystemFieldId($data);
        if ($fieldId === null) {
            return '';
        }

        return sprintf(
            '<img src="/assets/map/%d.png" class="lssSubspaceOverShield"
                style="z-index: %d; %s opacity:2;" />',
            $fieldId,
            PanelLayerEnum::SYSTEM->value,
            $panel->getHeightAndWidth()
        );
    }

    private function getSystemFieldId(MapData $data): ?int
    {
        if ($data->getMapfieldType() === self::ONLY_BACKGROUND_IMAGE) {
            return null;
        }

        return $data->getMapfieldType();
    }

    private function getSystemBackgroundId(MapData $data): string
    {
        return sprintf(
            '%02d%02d',
            $data->getPosY(),
            $data->getPosX()
        );
    }
}
