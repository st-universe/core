<?php

declare(strict_types=1);

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
        if ($this->getShip()->getSystemsId() > 0) {
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
        if ($this->getShip()->getSystemsId() > 0) {
            return array(
                "mx" => $this->getShip()->getSystem()->getMaxX(),
                "my" => $this->getShip()->getSystem()->getMaxY()
            );
        }
        return array(
            "mx" => MAP_MAX_X,
            "my" => MAP_MAX_Y
        );
    }

    function getLeft()
    {
        $coords = $this->getShipPosition();
        if ($coords['cx'] - 1 < 1) {
            return new Tuple("-", "disabled");
        }
        return new Tuple(($coords['cx'] - 1) . "|" . $coords['cy'], "");
    }

    function getRight()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cx'] + 1 > $borders['mx']) {
            return new Tuple("-", "disabled");
        }
        return new Tuple(($coords['cx'] + 1) . "|" . $coords['cy'], "");
    }

    function getUp()
    {
        $coords = $this->getShipPosition();
        if ($coords['cy'] - 1 < 1) {
            return new Tuple("-", "disabled");
        }
        return new Tuple($coords['cx'] . "|" . ($coords['cy'] - 1), "");
    }

    function getDown()
    {
        $coords = $this->getShipPosition();
        $borders = $this->getMapBorders();
        if ($coords['cy'] + 1 > $borders['my']) {
            return new Tuple("-", "disabled");
        }
        return new Tuple($coords['cx'] . "|" . ($coords['cy'] + 1), "");
    }
}