<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\ShipCountData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShipCountLayerRenderer implements LayerRendererInterface
{
    private bool $showCloakedEverywhere;

    private ?ShipInterface $currentShip;

    public function __construct(bool $showCloakedEverywhere, ?ShipInterface $currentShip)
    {
        $this->showCloakedEverywhere = $showCloakedEverywhere;
        $this->currentShip = $currentShip;
    }

    /** @param ShipCountData $data */
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

            $currentShip = $this->currentShip;

            if (
                $currentShip !== null
                && $currentShip->getTachyonState()
                && abs($data->getPosX() - $currentShip->getPosX()) <= $this->getTachyonRange($currentShip)
                && abs($data->getPosY() - $currentShip->getPosY()) <= $this->getTachyonRange($currentShip)
            ) {
                return "?";
            }
        }
        return null;
    }

    private function getTachyonRange(ShipInterface $ship): int
    {
        return $ship->isBase() ? 7 : 3;
    }
}
