<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Stu\Component\Ship\ShipRumpEnum;

class SignaturePanelEntry
{

    private $data = array();

    function __construct(
        &$entry = array()
    ) {
        $this->data = $entry;
    }

    function getPosX()
    {
        return $this->data['posx'];
    }

    function getPosY()
    {
        return $this->data['posy'];
    }

    function getMapfieldType()
    {
        return $this->data['type'];
    }

    function getShipCount()
    {
        return $this->data['shipcount'];
    }

    function getShieldState()
    {
        return $this->data['shieldstate'];
    }

    function hasShips()
    {
        return $this->data['shipcount'] > 0;
    }

    function getSubspaceCode()
    {
        $code = sprintf('%d%d%d%d', $this->getCode('d1c'), $this->getCode('d2c'), $this->getCode('d3c'), $this->getCode('d4c'));
        return $code == '0000' ? null : $code;
    }

    private function getCode(string $column): int
    {
        $shipCount = $this->data[$column];

        if ($shipCount == 0) {
            return 0;
        }
        if ($shipCount == 1) {
            return 1;
        }
        if ($shipCount < 6) {
            return 2;
        }
        if ($shipCount < 11) {
            return 3;
        }
        if ($shipCount < 21) {
            return 4;
        }

        return 5;
    }

    function getDisplayCount()
    {
        if ($this->hasShips()) {
            return $this->getShipCount();
        }
        return "";
    }

    function getCacheValue()
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getDisplayCount();
    }

    public $currentShipPosX = null;
    public $currentShipPosY = null;

    //obsolete?
    function getBorder()
    {
        return $this->data['color'];
    }

    private $cssClass = 'lss';

    function setCSSClass($class)
    {
        $this->cssClass = $class;
    }

    function getCSSClass()
    {
        return $this->cssClass;
    }

    function getOnClick()
    {
        if ($this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR) {
            return sprintf(
                'showSectorScanWindow(this, %d, %d, %d, %s);',
                $this->getPosX(),
                $this->getPosY(),
                $this->system ? $this->system->getId() : 0,
                $this->system ? 'false' : 'true'
            );
        }
        return sprintf('moveToPosition(%d,%d);', $this->getPosX(), $this->getPosY());
    }

    function getRow()
    {
        return $this->row;
    }
}
