<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class BorderLayerRenderer implements LayerRendererInterface
{
    public const string DEFAULT_BORDER_COLOR = '#2d2d2d';

    public function __construct(private ?SpacecraftInterface $currentSpacecraft, private ?bool $isOnShipLevel) {}

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
        if ($this->currentSpacecraft === null) {

            return self::DEFAULT_BORDER_COLOR;
        }

        // current position gets grey border
        if ($this->isCurrentShipPosition($data, $this->currentSpacecraft)) {
            return '#9b9b9b';
        }

        // hierarchy based border style
        if (
            $this->currentSpacecraft->getLssMode()->isBorderMode()
        ) {
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

        return self::DEFAULT_BORDER_COLOR;
    }

    private function isCurrentShipPosition(BorderData $data, SpacecraftInterface $currentSpacecraft): bool
    {
        return $this->isOnShipLevel === true
            && $data->getPosX() === $currentSpacecraft->getPosX()
            && $data->getPosY() === $currentSpacecraft->getPosY();
    }
}
