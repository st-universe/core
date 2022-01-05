<?php

declare(strict_types=1);

use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

class VisualNavPanelEntry
{

    private $data = array();

    private $isTachyonSystemActive;

    private $tachyonFresh;

    private $ship;

    private $tachyonRange;

    private $system;

    function __construct(
        &$entry = array(),
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false,
        ShipInterface $ship = null,
        StarSystemInterface $system = null
    ) {
        $this->data = $entry;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->ship = $ship;
        $this->system = $system;
        $this->tachyonRange = $ship !== null ? ($ship->isBase() ? 7 : 3) : 0;
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

    function getShieldState()
    {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
        return $this->data['shieldstate'];
=======
        return $this->data['shieldState'];
>>>>>>> planet shield in LSS
=======
        return $this->data['shieldstate'];
>>>>>>> lower case
=======
        return $this->data['shieldState'];
>>>>>>> planet shield in LSS
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
                && abs($this->getPosX() - $this->currentShipPosX) < $this->tachyonRange
                && abs($this->getPosY() - $this->currentShipPosY) < $this->tachyonRange
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
        if ($this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR) {
            return true;
        }
        if (!$this->ship->canMove()) {
            return false;
        }
        if (!$this->isCurrentShipPosition() && ($this->getPosX() == $this->currentShipPosX || $this->getPosY() == $this->currentShipPosY)) {
            return true;
        }
        return false;
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
