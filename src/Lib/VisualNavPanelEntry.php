<?php

declare(strict_types=1);

class VisualNavPanelEntry
{

    private $data = array();

    private $isTachyonSystemActive;

    private $tachyonFresh;

    function __construct(&$entry = array(), bool $isTachyonSystemActive = false, bool $tachyonFresh = false)
    {
        $this->data = $entry;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
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

    function hasCloakedShips()
    {
        return $this->data['cloakcount'] > 0;
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
        if ($this->hasCloakedShips()) {
            if ($this->tachyonFresh) {
                return "?";
            }
            if (
                $this->isTachyonSystemActive
                && abs($this->getPosX() - $this->currentShipPosX) < 3
                && abs($this->getPosY() - $this->currentShipPosY) < 3
            ) {
                return "?";
            }
        }
        return "";
    }

    function getCacheValue()
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getDisplayCount() . "_" . $this->isClickAble();
    }

    public $currentShipPosX = null;
    public $currentShipPosY = null;

    function isCurrentShipPosition()
    {
        if ($this->getPosX() == $this->currentShipPosX && $this->getPosY() == $this->currentShipPosY) {
            return true;
        }
        return false;
    }

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
        if (!$this->getRow() && $this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return $this->cssClass;
    }

    function isClickAble()
    {
        if (!$this->isCurrentShipPosition() && ($this->getPosX() == $this->currentShipPosX || $this->getPosY() == $this->currentShipPosY)) {
            return true;
        }
        return false;
    }

    function getRow()
    {
        return $this->row;
    }
}
