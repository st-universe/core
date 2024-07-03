<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Component\Ship\ShipRumpEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Orm\Entity\ShipInterface;

class VisualNavPanelEntry extends SignaturePanelEntry
{
    private ShipInterface $currentShip;

    private bool $isOnShipLevel;

    public function __construct(
        int $x,
        int $y,
        bool $isOnShipLevel,
        PanelLayers $layers,
        ShipInterface $currentShip
    ) {
        parent::__construct($x, $y, $layers);
        $this->currentShip = $currentShip;
        $this->isOnShipLevel = $isOnShipLevel;
    }

    private function isCurrentShipPosition(): bool
    {
        if (!$this->isOnShipLevel) {
            return false;
        }

        if ($this->x !== $this->currentShip->getPosX()) {
            return false;
        }
        return $this->y === $this->currentShip->getPosY();
    }

    public function getCssClass(): string
    {
        if ($this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return parent::getCssClass();
    }

    public function isClickAble(): bool
    {
        if (
            $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            return true;
        }
        if (!$this->currentShip->canMove()) {
            return false;
        }

        return !$this->isCurrentShipPosition()
            && ($this->x === $this->currentShip->getPosX() || $this->y === $this->currentShip->getPosY());
    }

    public function getOnClick(): string
    {
        if (
            $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            return sprintf(
                'showSectorScanWindow(this, %d, %d, %d, %s);',
                $this->x,
                $this->y,
                0,
                'true'
            );
        }
        return sprintf('moveToPosition(%d,%d);', $this->x, $this->y);
    }
}
