<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\ShipCountData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class ShipCountLayerRenderer implements LayerRendererInterface
{
    public function __construct(private bool $showCloakedEverywhere, private ?SpacecraftInterface $currentSpacecraft) {}

    /** @param ShipCountData $data */
    #[Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        $displayCount = $this->getDisplayCount($data);
        if ($displayCount === null) {
            return '';
        }

        return sprintf(
            '<div style="%s z-index: %d;" class="centered">%s</div>',
            $panel->getFontSize(),
            PanelLayerEnum::SHIP_COUNT->value,
            $displayCount
        );
    }

    private function getDisplayCount(ShipCountData $data): ?string
    {
        if ($data->getShipCount() > 0) {
            return (string) $data->getShipCount();
        }
        if ($data->hasCloakedShips()) {
            if ($this->showCloakedEverywhere) {
                return "?";
            }

            $currentSpacecraft = $this->currentSpacecraft;

            if (
                $currentSpacecraft !== null
                && $currentSpacecraft->getTachyonState()
                && abs($data->getPosX() - $currentSpacecraft->getPosX()) <= $this->getTachyonRange($currentSpacecraft)
                && abs($data->getPosY() - $currentSpacecraft->getPosY()) <= $this->getTachyonRange($currentSpacecraft)
            ) {
                return "?";
            }
        }
        return null;
    }

    private function getTachyonRange(SpacecraftInterface $spacecraft): int
    {
        return $spacecraft->isStation() ? 7 : 3;
    }
}
