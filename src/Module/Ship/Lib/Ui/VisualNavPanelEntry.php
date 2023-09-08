<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

class VisualNavPanelEntry
{
    //TODO set currentShipInfo as Bean
    /** @var null|int */
    public $currentShipPosX;

    /** @var null|int */
    public $currentShipPosY;

    /** @var null|int */
    public $currentShipSysId;

    private ?int $row = null;

    private ?VisualNavPanelEntryData $data;

    private ?LayerInterface $layer;

    private ?EncodedMapInterface $encodedMap;

    private bool $isTachyonSystemActive;

    private bool $tachyonFresh;

    private ?ShipInterface $ship;

    private int $tachyonRange;

    private ?StarSystemInterface $system;

    private string $cssClass = 'lss';

    public function __construct(
        ?VisualNavPanelEntryData $data,
        ?LayerInterface $layer,
        ?EncodedMapInterface $encodedMap,
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false,
        ShipInterface $ship = null,
        StarSystemInterface $system = null
    ) {
        $this->data = $data;
        $this->layer = $layer;
        $this->encodedMap = $encodedMap;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->ship = $ship;
        $this->system = $system;
        $this->tachyonRange = $ship !== null ? ($ship->isBase() ? 7 : 3) : 0;
    }

    private function getData(): VisualNavPanelEntryData
    {
        $data = $this->data;
        if ($data === null) {
            throw new RuntimeException('should not happen');
        }
        return $data;
    }

    public function getLssCellData(): LssCellData
    {
        return new LssCellData(
            $this->getSystemBackgroundId(),
            $this->getFieldGraphicID(),
            $this->getMapGraphicPath(),
            $this->getData()->getShieldState(),
            $this->getSubspaceCode(),
            $this->getDisplayCount(),
        );
    }

    private function getFieldGraphicID(): int
    {
        $fieldId = $this->getData()->getMapfieldType();

        if ($fieldId === 1) {
            return 0;
        } else {

            return $fieldId;
        }
    }

    private function getSystemBackgroundId(): string
    {
        return sprintf(
            '%02d%02d',
            $this->getData()->getPosY(),
            $this->getData()->getPosX()
        );
    }

    private function getMapGraphicPath(): string
    {
        $layer = $this->layer;
        if ($layer === null) {
            throw new RuntimeException('should not happen');
        }

        if ($layer->isEncoded()) {
            $encodedMap = $this->encodedMap;
            if ($encodedMap === null) {
                throw new RuntimeException('should not happen');
            }

            return $encodedMap->getEncodedMapPath(
                $this->getData()->getMapfieldType(),
                $layer
            );
        }

        return sprintf('%d/%d.png', $layer->getId(), $this->getData()->getMapfieldType());
    }

    private function getSubspaceCode(): ?string
    {
        $code = sprintf(
            '%d%d%d%d',
            $this->getCode($this->getData()->getDirection1Count()),
            $this->getCode($this->getData()->getDirection2Count()),
            $this->getCode($this->getData()->getDirection3Count()),
            $this->getCode($this->getData()->getDirection4Count())
        );
        return $code == '0000' ? null : $code;
    }

    private function getCode(?int $value): int
    {
        $shipCount = $value ?? 0;

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

    private function getDisplayCount(): string
    {
        if ($this->getData()->getShipCount() > 0) {
            return (string) $this->getData()->getShipCount();
        }
        if ($this->getData()->hasCloakedShips()) {
            if ($this->tachyonFresh) {
                return "?";
            }

            if (
                $this->isTachyonSystemActive
                && abs($this->getData()->getPosX() - $this->currentShipPosX) < $this->tachyonRange
                && abs($this->getData()->getPosY() - $this->currentShipPosY) < $this->tachyonRange
            ) {
                return "?";
            }
        }
        return "";
    }

    private function isCurrentShipPosition(): bool
    {
        return $this->getData()->getSystemId() == $this->currentShipSysId
            && $this->getData()->getPosX() == $this->currentShipPosX
            && $this->getData()->getPosY() == $this->currentShipPosY;
    }

    public function getBorder(): string
    {
        // current position gets grey border
        if (!$this->getRow() && $this->isCurrentShipPosition()) {
            return '#9b9b9b';
        }

        // hierarchy based border style
        if (
            $this->ship !== null &&
            $this->ship->getLSSmode() == ShipLSSModeEnum::LSS_BORDER
        ) {
            $factionColor = $this->getData()->getFactionColor();
            if (!empty($factionColor)) {
                return $factionColor;
            }

            $allyColor = $this->getData()->getAllyColor();
            if (!empty($allyColor)) {
                return $allyColor;
            }

            $userColor = $this->getData()->getUserColor();
            if (!empty($userColor)) {
                return $userColor;
            }
        }

        // default border style
        return '#2d2d2d';
    }

    public function setCSSClass(string $class): VisualNavPanelEntry
    {
        $this->cssClass = $class;

        return $this;
    }

    public function getCSSClass(): string
    {
        if (!$this->getRow() && $this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return $this->cssClass;
    }

    public function isClickAble(): bool
    {
        if ($this->ship === null) {
            return false;
        }
        if ($this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR || $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE) {
            return true;
        }
        if (!$this->ship->canMove()) {
            return false;
        }
        return !$this->isCurrentShipPosition()
            && ($this->getData()->getPosX() == $this->currentShipPosX || $this->getData()->getPosY() == $this->currentShipPosY);
    }

    public function getOnClick(): string
    {
        if ($this->ship === null) {
            return '';
        }

        if (
            $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            return sprintf(
                'showSectorScanWindow(this, %d, %d, %d, %s);',
                $this->getData()->getPosX(),
                $this->getData()->getPosY(),
                $this->system !== null ? $this->system->getId() : 0,
                $this->system !== null ? 'false' : 'true'
            );
        }
        return sprintf('moveToPosition(%d,%d);', $this->getData()->getPosX(), $this->getData()->getPosY());
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): VisualNavPanelEntry
    {
        $this->row = $row;

        return $this;
    }
}
