<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftCountData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\Spacecraft;

final class SpacecraftCountLayerRenderer implements LayerRendererInterface
{
    public function __construct(private bool $showCloakedEverywhere, private ?Spacecraft $currentSpacecraft) {}

    /** @param SpacecraftCountData $data */
    #[\Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        $displayCount = $this->getDisplayCount($data);
        if ($displayCount === null) {
            return '';
        }

        return sprintf(
            '<div style="%s z-index: %d;" class="centered">%s</div>',
            $panel->getFontSize(),
            PanelLayerEnum::SPACECRAFT_COUNT->value,
            $displayCount
        );
    }

    private function getDisplayCount(SpacecraftCountData $data): ?string
    {
        if (!$data->isEnabled()) {
            return null;
        }

        $spacecraftCount = $data->getSpacecraftCount();
        if ($spacecraftCount > 0) {
            return $data->isDubious() ? '!'  : (string) $spacecraftCount;
        }
        if ($data->hasCloakedShips()) {
            if ($this->showCloakedEverywhere) {
                return $data->isDubious() ? '!'  : "?";
            }

            $currentSpacecraft = $this->currentSpacecraft;

            if (
                $currentSpacecraft !== null
                && $currentSpacecraft->getTachyonState()
                && abs($data->getPosX() - $currentSpacecraft->getPosX()) <= $this->getTachyonRange($currentSpacecraft)
                && abs($data->getPosY() - $currentSpacecraft->getPosY()) <= $this->getTachyonRange($currentSpacecraft)
            ) {
                return $data->isDubious() ? '!'  : "?";
            }
        }
        return null;
    }

    private function getTachyonRange(Spacecraft $spacecraft): int
    {
        return $spacecraft->isStation() ? 7 : 3;
    }
}
