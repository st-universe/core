<?php

declare(strict_types=1);

use Stu\Component\Map\MapEnum;
use Stu\Lib\NavPanelButton;
use Stu\Orm\Entity\ShipInterface;

class NavPanel
{

    private $ship;

    function __construct(ShipInterface $ship)
    {
        $this->ship = $ship;
    }

    function getShip()
    {
        return $this->ship;
    }

    function getShipPosition()
    {
        if ($this->getShip()->getSystem() !== null) {
            return array(
                "cx" => $this->getShip()->getSX(),
                "cy" => $this->getShip()->getSY()
            );
        }
        return array(
            "cx" => $this->getShip()->getCX(),
            "cy" => $this->getShip()->getCY()
        );
    }

    function getMapBorders()
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

    function getLeft()
    {
        $coords = $this->getShipPosition();
        if ($coords['cx'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] - 1) . "|" . $coords['cy']);
    }

    function getRight()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cx'] + 1 > $borders['mx']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton(($coords['cx'] + 1) . "|" . $coords['cy']);
    }

    function getUp()
    {
        $coords = $this->getShipPosition();
        if ($coords['cy'] - 1 < 1) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] - 1));
    }

    function getDown()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cy'] + 1 > $borders['my']) {
            return new NavPanelButton("-", true);
        }
        return new NavPanelButton($coords['cx'] . "|" . ($coords['cy'] + 1));
    }
}
