<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

class VisualNavPanelEntry
{
    /** @var null|int */
    public $currentShipPosX;

    /** @var null|int */
    public $currentShipPosY;

    /** @var null|int */
    public $currentShipSysId;

    public ?int $row = null;

    /**
     * @var array{
     *     posx: int,
     *     posy: int,
     *     sysid: null|int,
     *     shipcount: int,
     *     cloakcount: int,
     *     allycolor: string,
     *     usercolor: string,
     *     factioncolor: string,
     *     shieldstate: null|bool,
     *     type: int,
     *    layer: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * }
     */
    private array $data;

    private bool $isTachyonSystemActive;

    private bool $tachyonFresh;

    private ?ShipInterface $ship;

    private int $tachyonRange;

    private ?StarSystemInterface $system;

    private string $cssClass = 'lss';

    /**
     * @param array{
     *     posx: int,
     *     posy: int,
     *     sysid: ?int,
     *     shipcount: int,
     *     cloakcount: int,
     *     allycolor: string,
     *     usercolor: string,
     *     factioncolor: string,
     *     shieldstate: null|bool,
     *     type: int,
     *     layer: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * } $entry
     */
    public function __construct(
        array &$entry,
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

    public function getPosX(): int
    {
        return $this->data['posx'];
    }

    public function getPosY(): int
    {
        return $this->data['posy'];
    }

    public function getSystemId(): int
    {
        return $this->data['sysid'] ?? 0;
    }

    public function getMapfieldType(): int
    {
        return $this->data['type'];
    }

    public function getFieldGraphicID(): int
    {
        $fieldId = $this->getMapfieldType();


        if ($fieldId === 1) {
            return 0;
        } else {

            return $fieldId;
        }
    }

    public function getSystemBackgroundId(): string
    {

        $x = (string)$this->getPosX();
        $y = (string)$this->getPosY();

        $x = str_pad($x, 2, '0', STR_PAD_LEFT);
        $y = str_pad($y, 2, '0', STR_PAD_LEFT);

        $backgroundId = $y . $x;

        return $backgroundId;
    }


    public function getLayer(): ?int
    {
        return $this->data['layer'];
    }

    public function getShipCount(): int
    {
        return $this->data['shipcount'];
    }

    public function hasCloakedShips(): bool
    {
        return $this->data['cloakcount'] > 0;
    }

    public function getShieldState(): bool
    {
        return $this->data['shieldstate'] ?? false;
    }

    public function hasShips(): bool
    {
        return $this->data['shipcount'] > 0;
    }

    public function getSubspaceCode(): ?string
    {
        $code = sprintf('%d%d%d%d', $this->getCode('d1c'), $this->getCode('d2c'), $this->getCode('d3c'), $this->getCode('d4c'));
        return $code == '0000' ? null : $code;
    }

    private function getCode(string $column): int
    {
        $shipCount = $this->data[$column] ?? 0;

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

    public function getDisplayCount(): string
    {
        if ($this->hasShips()) {
            return (string) $this->getShipCount();
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

    public function getCacheValue(): string
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getLayer() . "_" . $this->getDisplayCount() . "_" . $this->isClickAble() . "_" . $this->getBorder();
    }

    public function isCurrentShipPosition(): bool
    {
        return $this->getSystemId() == $this->currentShipSysId
            && $this->getPosX() == $this->currentShipPosX
            && $this->getPosY() == $this->currentShipPosY;
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
            $factionColor = $this->data['factioncolor'] ?? null;
            if (!empty($factionColor)) {
                return $factionColor;
            }

            $allyColor = $this->data['allycolor'] ?? null;
            if (!empty($allyColor)) {
                return $allyColor;
            }

            $userColor = $this->data['usercolor'] ?? null;
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
        return !$this->isCurrentShipPosition() && ($this->getPosX() == $this->currentShipPosX || $this->getPosY() == $this->currentShipPosY);
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
                $this->getPosX(),
                $this->getPosY(),
                $this->system !== null ? $this->system->getId() : 0,
                $this->system !== null ? 'false' : 'true'
            );
        }
        return sprintf('moveToPosition(%d,%d);', $this->getPosX(), $this->getPosY());
    }

    public function getRow(): ?int
    {
        return $this->row;
    }
}
