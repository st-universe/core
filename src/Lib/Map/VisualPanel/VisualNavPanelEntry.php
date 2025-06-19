<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Override;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Orm\Entity\SpacecraftInterface;

class VisualNavPanelEntry extends SignaturePanelEntry
{
    public function __construct(
        int $x,
        int $y,
        private bool $isOnShipLevel,
        PanelLayers $layers,
        private SpacecraftInterface $currentSpacecraft
    ) {
        parent::__construct($x, $y, $layers);
    }

    private function isCurrentShipPosition(): bool
    {
        if (!$this->isOnShipLevel) {
            return false;
        }

        if ($this->x !== $this->currentSpacecraft->getPosX()) {
            return false;
        }
        return $this->y === $this->currentSpacecraft->getPosY();
    }

    #[Override]
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
            $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR
            || $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_BASE
        ) {
            return true;
        }
        if (!$this->currentSpacecraft->canMove()) {
            return false;
        }

        return !$this->isCurrentShipPosition()
            && ($this->x === $this->currentSpacecraft->getPosX() || $this->y === $this->currentSpacecraft->getPosY());
    }

    public function getOnClick(): string
    {
        if (
            $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR
            || $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_BASE
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
