<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Orm\Entity\ShipInterface;

final class BorderLayerRenderer implements LayerRendererInterface
{
    public const DEFAULT_BORDER_COLOR = '#2d2d2d';

    private ?ShipInterface $currentShip;

    private ?bool $isOnShipLevel;

    public function __construct(?ShipInterface $currentShip, ?bool $isOnShipLevel)
    {
        $this->currentShip = $currentShip;
        $this->isOnShipLevel = $isOnShipLevel;
    }

    /** @param BorderData $data */
    public function render(CellDataInterface $data, AbstractVisualPanel $panel): string
    {
        return sprintf(
            'border:1px solid %s;',
            $this->getBorderColor($data)
        );
    }

    public function getBorderColor(BorderData $data): string
    {
        if ($this->currentShip === null) {

            return self::DEFAULT_BORDER_COLOR;
        }

        // current position gets grey border
        if ($this->isCurrentShipPosition($data, $this->currentShip)) {
            return '#9b9b9b';
        }

        // hierarchy based border style
        if (
            $this->currentShip->getLSSmode() == ShipLSSModeEnum::LSS_BORDER
        ) {
            $factionColor = $data->getFactionColor();
            if (!empty($factionColor)) {
                return $factionColor;
            }

            $allyColor = $data->getAllyColor();
            if (!empty($allyColor)) {
                return $allyColor;
            }

            $userColor = $data->getUserColor();
            if (!empty($userColor)) {
                return $userColor;
            }
        }

        return self::DEFAULT_BORDER_COLOR;
    }

    private function isCurrentShipPosition(BorderData $data, ShipInterface $currentShip): bool
    {
        return $this->isOnShipLevel === true
            && $data->getPosX() == $currentShip->getPosX()
            && $data->getPosY() == $currentShip->getPosY();
    }
}
