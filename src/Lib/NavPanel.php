<?php

declare(strict_types=1);

use Stu\Lib\NavPanelButton;
use Stu\Lib\NavPanelButtonInterface;
use Stu\Orm\Entity\ShipInterface;

class NavPanel
{
    private ShipInterface $ship;

    public function __construct(ShipInterface $ship)
    {
        $this->ship = $ship;
    }

    public function getShip()
    {
        return $this->ship;
    }

    /** @return array{cx: int, cy: int} */
    public function getShipPosition(): array
    {
        if ($this->getShip()->getSystem() !== null) {
            return [
                "cx" => $this->getShip()->getSX(),
                "cy" => $this->getShip()->getSY()
            ];
        }
        return [
            "cx" => $this->getShip()->getMap()->getCx(),
            "cy" => $this->getShip()->getMap()->getCy()
        ];
    }

    /** @return array{mx: int, my: int} */
    public function getMapBorders(): array
    {
        $starSystem = $this->getShip()->getSystem();

        if ($starSystem !== null) {
            return [
                "mx" => $starSystem->getMaxX(),
                "my" => $starSystem->getMaxY()
            ];
        }

        $layer = $this->getShip()->getLayer();
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
