<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;

class VisualNavPanelEntry extends SignaturePanelEntry
{
    private ShipInterface $currentShip;

    private bool $isTachyonSystemActive;

    private bool $tachyonFresh;

    public function __construct(
        VisualPanelEntryData $data,
        ?LayerInterface $layer,
        EncodedMapInterface $encodedMap,
        ShipInterface $currentShip,
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false
    ) {
        parent::__construct($data, $layer, $encodedMap);
        $this->currentShip = $currentShip;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
    }

    protected function getDisplayCount(): ?string
    {
        if ($this->data->getShipCount() > 0) {
            return (string) $this->data->getShipCount();
        }
        if ($this->data->hasCloakedShips()) {
            if ($this->tachyonFresh) {
                return "?";
            }

            if (
                $this->isTachyonSystemActive
                && abs($this->data->getPosX() - $this->currentShip->getPosX()) < $this->getTachyonRange()
                && abs($this->data->getPosY() - $this->currentShip->getPosY()) < $this->getTachyonRange()
            ) {
                return "?";
            }
        }
        return null;
    }

    private function getTachyonRange(): int
    {
        return $this->currentShip->isBase() ? 7 : 3;
    }

    private function isCurrentShipPosition(): bool
    {
        return $this->data->getSystemId() == $this->currentShip->getSystemsId()
            && $this->data->getPosX() == $this->currentShip->getPosX()
            && $this->data->getPosY() == $this->currentShip->getPosY();
    }

    public function getBorder(): string
    {
        // current position gets grey border
        if ($this->isCurrentShipPosition()) {
            return '#9b9b9b';
        }

        // hierarchy based border style
        if (
            $this->currentShip->getLSSmode() == ShipLSSModeEnum::LSS_BORDER
        ) {
            $factionColor = $this->data->getFactionColor();
            if (!empty($factionColor)) {
                return $factionColor;
            }

            $allyColor = $this->data->getAllyColor();
            if (!empty($allyColor)) {
                return $allyColor;
            }

            $userColor = $this->data->getUserColor();
            if (!empty($userColor)) {
                return $userColor;
            }
        }

        // default border style
        return '#2d2d2d';
    }

    public function getCSSClass(): string
    {
        if ($this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return parent::getCSSClass();
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
        return !$this->isCurrentShipPosition();
    }

    public function getOnClick(): string
    {
        if (
            $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            return sprintf(
                'showSectorScanWindow(this, %d, %d, %d, %s);',
                $this->data->getPosX(),
                $this->data->getPosY(),
                0,
                'true'
            );
        }
        return sprintf('moveToPosition(%d,%d);', $this->data->getPosX(), $this->data->getPosY());
    }

    public function isRowIndex(): bool
    {
        return false;
    }
}
