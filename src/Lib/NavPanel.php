<?php

declare(strict_types=1);

use Stu\Lib\NavPanelButton;
use Stu\Orm\Entity\ShipInterface;

class NavPanel
{
    private $ship;

    public function __construct(ShipInterface $ship)
    {
        $this->ship = $ship;
    }

    public function getShip()
    {
        return $this->ship;
    }

    public function getShipPosition()
    {
        if ($this->getShip()->getSystem() !== null) {
            return [
                "cx" => $this->getShip()->getSX(),
                "cy" => $this->getShip()->getSY()
            ];
        }
        return [
            "cx" => $this->getShip()->getCX(),
            "cy" => $this->getShip()->getCY()
        ];
    }

    public function getMapBorders()
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

    public function getLeft()
    {
        $coords = $this->getShipPosition();
        if ($coords['cx'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] - 1) . "|" . $coords['cy']);
    }

    public function getRight()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cx'] + 1 > $borders['mx']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] + 1) . "|" . $coords['cy']);
    }

    public function getUp()
    {
        $coords = $this->getShipPosition();
        if ($coords['cy'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] - 1));
    }

    public function getDown()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cy'] + 1 > $borders['my']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] + 1));
    }
}
