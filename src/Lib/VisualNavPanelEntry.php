<?php

declare(strict_types=1);

class VisualNavPanelEntry
{

    private $data = array();

    private $isTachyonSystemActive;

    function __construct(&$entry = array(), bool $isTachyonSystemActive = false)
    {
        $this->data = $entry;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
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

    function getDisplayCount()
    {
        if ($this->hasShips()) {
            return $this->getShipCount();
        }
        if ($this->hasCloakedShips()) {
            if ($this->isTachyonSystemActive
                && abs($this->getPosX(), $this->currentShipPosX) < 3
                && abs($this->getPosY(), $this->currentShipPosY) < 3)
            {
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