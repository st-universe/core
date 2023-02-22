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
    public $currentShipPosX = null;

    /** @var null|int */
    public $currentShipPosY = null;

    /** @var null|int */
    public $currentShipSysId = null;

    /** @var int */
    public $row;

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
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * } $entry
     */
    public function __construct(
        array &$entry = [],
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

    public function getPosX()
    {
        return $this->data['posx'];
    }

    public function getPosY()
    {
        return $this->data['posy'];
    }

    public function getSystemId()
    {
        return $this->data['sysid'] ?? 0;
    }

    public function getMapfieldType()
    {
        return $this->data['type'];
    }

    public function getShipCount()
    {
        return $this->data['shipcount'];
    }

    public function hasCloakedShips()
    {
        return $this->data['cloakcount'] > 0;
    }

    public function getShieldState()
    {
        return $this->data['shieldstate'] ?? false;
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

    public function getDisplayCount()
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

    public function getCacheValue()
    {
        return $this->getPosX() . "_" . $this->getPosY() . "_" . $this->getMapfieldType() . "_" . $this->getDisplayCount() . "_" . $this->isClickAble() . "_" . $this->getBorder();
    }

    public function isCurrentShipPosition()
    {
        if (
            $this->getSystemId() == $this->currentShipSysId
            && $this->getPosX() == $this->currentShipPosX
            && $this->getPosY() == $this->currentShipPosY
        ) {
            return true;
        }
        return false;
    }

    public function getBorder()
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
            $factionColor = $this->data['factioncolor'];
            if (!empty($factionColor)) {
                return $factionColor;
            }

            $allyColor = $this->data['allycolor'];
            if (!empty($allyColor)) {
                return $allyColor;
            }

            $userColor = $this->data['usercolor'];
            if (!empty($userColor)) {
                return $userColor;
            }
        }

        // default border style
        return '#2d2d2d';
    }

    public function setCSSClass($class)
    {
        $this->cssClass = $class;
    }

    public function getCSSClass()
    {
        if (!$this->getRow() && $this->isCurrentShipPosition()) {
            return 'lss_current';
        }
        return $this->cssClass;
    }

    public function isClickAble()
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

    public function getOnClick(): string
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

    public function getRow(): ?int
    {
        return $this->row;
    }
}
