<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Override;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

final class BorderLayerRenderer implements LayerRendererInterface
{
    public const string DEFAULT_BORDER_COLOR = '#2d2d2d';

    public function __construct(
        private ?SpacecraftWrapperInterface $currentWrapper,
        private ?bool $isOnShipLevel
    ) {}

    /** @param BorderData $data */
    #[Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        return sprintf(
            'border:1px solid %s;',
            $this->getBorderColor($data)
        );
    }

    public function getBorderColor(BorderData $data): string
    {
        if ($this->currentWrapper === null) {
            return self::DEFAULT_BORDER_COLOR;
        }

        // current position gets grey border
        if ($this->isCurrentShipPosition($data, $this->currentWrapper->get())) {
            return '#9b9b9b';
        }

        $lss = $this->currentWrapper->getLssSystemData();

        // hierarchy based border style
        if ($lss !== null && $lss->getMode()->isBorderMode()) {
            if ($lss->getMode() === SpacecraftLssModeEnum::BORDER) {
                $factionColor = $data->getFactionColor();
                if ($factionColor !== null && $factionColor !== '' && $factionColor !== '0') {
                    return $factionColor;
                }

                $allyColor = $data->getAllyColor();
                if ($allyColor !== null && $allyColor !== '' && $allyColor !== '0') {
                    return $allyColor;
                }

                $userColor = $data->getUserColor();
                if ($userColor !== null && $userColor !== '' && $userColor !== '0') {
                    return $userColor;
                }
            }
            if ($lss->getMode() === SpacecraftLssModeEnum::IMPASSABLE) {
                $impassablecolor = $data->getImpassable();
                if ($impassablecolor != true) {
                    if ($data->getComplementaryColor() !== null && $data->getComplementaryColor() !== '' && $data->getComplementaryColor() !== '0') {
                        return $data->getComplementaryColor();
                    } else {
                        return '#730505';
                    }
                }
            }
            if ($lss->getMode() === SpacecraftLssModeEnum::CARTOGRAPHING) {
                $cartographingcolor = $data->getCartographing();
                if ($cartographingcolor != false) {
                    if ($data->getComplementaryColor() !== null && $data->getComplementaryColor() !== '' && $data->getComplementaryColor() !== '0') {
                        return $data->getComplementaryColor();
                    } else {
                        return '#730505';
                    }
                }
            }
        }

        return self::DEFAULT_BORDER_COLOR;
    }

    private function isCurrentShipPosition(BorderData $data, Spacecraft $currentSpacecraft): bool
    {
        return $this->isOnShipLevel === true
            && $data->getPosX() === $currentSpacecraft->getPosX()
            && $data->getPosY() === $currentSpacecraft->getPosY();
    }
}
