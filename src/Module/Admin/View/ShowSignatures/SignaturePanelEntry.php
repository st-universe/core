<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

class SignaturePanelEntry
{
    private $data = [];

    private ?int $row = null;

    public function __construct(
        &$entry = []
    ) {
        $this->data = $entry;
    }

    public function getPosX()
    {
        return $this->data['posx'];
    }

    public function getPosY()
    {
        return $this->data['posy'];
    }

    public function getMapfieldType()
    {
        return $this->data['type'];
    }

    public function getShipCount()
    {
        return $this->data['shipcount'];
    }

    public function getShieldState()
    {
        return $this->data['shieldstate'];
    }

    public function hasShips()
    {
        return $this->data['shipcount'] > 0;
    }

    public function getSubspaceCode()
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

    public function getDisplayCount()
    {
        if ($this->hasShips()) {
            return $this->getShipCount();
        }
        return "";
    }

    public function getCacheValue()
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getDisplayCount();
    }

    public $currentShipPosX = null;
    public $currentShipPosY = null;

    //obsolete?
    public function getBorder()
    {
        return $this->data['color'];
    }

    private $cssClass = 'lss';

    public function setCSSClass($class)
    {
        $this->cssClass = $class;
    }

    public function getCSSClass()
    {
        return $this->cssClass;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): void
    {
        $this->row = $row;
    }
}
