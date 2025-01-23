<?php

declare(strict_types=1);

namespace Stu\Lib\Map\NavPanel;

use RuntimeException;
use Stu\Orm\Entity\SpacecraftInterface;

class NavPanel
{
    public function __construct(private SpacecraftInterface $spacecraft) {}

    public function getSpacecraft(): SpacecraftInterface
    {
        return $this->spacecraft;
    }

    /** @return array{cx: int, cy: int} */
    public function getShipPosition(): array
    {
        return [
            "cx" => $this->getSpacecraft()->getPosX(),
            "cy" => $this->getSpacecraft()->getPosY()
        ];
    }

    /** @return array{mx: int, my: int} */
    public function getMapBorders(): array
    {
        $starSystem = $this->getSpacecraft()->getSystem();

        if ($starSystem !== null) {
            return [
                "mx" => $starSystem->getMaxX(),
                "my" => $starSystem->getMaxY()
            ];
        }

        $layer = $this->getSpacecraft()->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }
        return [
            "mx" => $layer->getWidth(),
            "my" => $layer->getHeight()
        ];
    }

    public function getLeft(): NavPanelButtonInterface
    {
        $coords = $this->getShipPosition();
        if ($coords['cx'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] - 1) . "|" . $coords['cy']);
    }

    public function getRight(): NavPanelButtonInterface
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cx'] + 1 > $borders['mx']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] + 1) . "|" . $coords['cy']);
    }

    public function getUp(): NavPanelButtonInterface
    {
        $coords = $this->getShipPosition();
        if ($coords['cy'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] - 1));
    }

    public function getDown(): NavPanelButtonInterface
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cy'] + 1 > $borders['my']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] + 1));
    }
}
