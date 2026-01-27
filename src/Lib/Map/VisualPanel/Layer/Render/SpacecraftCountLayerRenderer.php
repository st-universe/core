<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftCountData;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class SpacecraftCountLayerRenderer implements LayerRendererInterface
{
    public function __construct(
        private bool $showCloakedEverywhere,
        private ?Spacecraft $currentSpacecraft,
        private StarSystemRepositoryInterface $starSystemRepository
    ) {}

    /** @param SpacecraftCountData $data */
    #[\Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        $displayCount = $this->getDisplayCount($data);
        if ($displayCount === null) {
            return '';
        }

        return sprintf(
            '<div style="%s z-index: %d;" class="centered">%s</div>',
            $panel->getFontSize(),
            PanelLayerEnum::SPACECRAFT_COUNT->value,
            $displayCount
        );
    }

    private function getDisplayCount(SpacecraftCountData $data): ?string
    {
        if (!$data->isEnabled()) {
            return null;
        }

        $spacecraftCount = $data->getSpacecraftCount();

        if ($spacecraftCount > 0) {
            return $data->isDubious() ? '!'  : (string) $spacecraftCount;
        }

        if ($data->hasCloakedShips()) {
            if ($this->showCloakedEverywhere) {
                return $data->isDubious() ? '!'  : "?";
            }

            $currentSpacecraft = $this->currentSpacecraft;

            if ($currentSpacecraft !== null && $currentSpacecraft->getTachyonState()) {
                $rump = $currentSpacecraft->getRump();

                if (
                    $rump->getRoleId() === SpacecraftRumpRoleEnum::SENSOR
                    || $rump->getRoleId() === SpacecraftRumpRoleEnum::BASE
                ) {
                    $spacecraftX = $this->getRelevantXCoordinate($currentSpacecraft, $data);
                    $spacecraftY = $this->getRelevantYCoordinate($currentSpacecraft, $data);
                    $dataX = $this->getRelevantDataXCoordinate($data);
                    $dataY = $this->getRelevantDataYCoordinate($data);

                    $distanceX = abs($dataX - $spacecraftX);
                    $distanceY = abs($dataY - $spacecraftY);
                    $range = $this->getTachyonRange($currentSpacecraft);

                    if ($distanceX <= $range && $distanceY <= $range) {
                        return $data->isDubious() ? '!'  : "?";
                    }
                } elseif (
                    abs($data->getPosX() - $currentSpacecraft->getPosX()) <= $this->getTachyonRange($currentSpacecraft)
                    && abs($data->getPosY() - $currentSpacecraft->getPosY()) <= $this->getTachyonRange($currentSpacecraft)
                ) {
                    return $data->isDubious() ? '!'  : "?";
                }
            }
        }
        return null;
    }


    private function getTachyonRange(Spacecraft $spacecraft): int
    {
        return $spacecraft->isStation() ? 7 : 3;
    }

    private function getRelevantXCoordinate(Spacecraft $spacecraft, SpacecraftCountData $data): int
    {
        $spacecraftSystemMap = $spacecraft->getStarsystemMap();

        if ($spacecraftSystemMap !== null) {
            return $spacecraftSystemMap->getSystem()->getCx() ?? $spacecraft->getPosX();
        }

        return $spacecraft->getPosX();
    }

    private function getRelevantYCoordinate(Spacecraft $spacecraft, SpacecraftCountData $data): int
    {
        $spacecraftSystemMap = $spacecraft->getStarsystemMap();

        if ($spacecraftSystemMap !== null) {
            return $spacecraftSystemMap->getSystem()->getCy() ?? $spacecraft->getPosY();
        }

        return $spacecraft->getPosY();
    }

    private function getRelevantDataXCoordinate(SpacecraftCountData $data): int
    {
        $dataSystemId = $data->getSystemId();
        if ($dataSystemId !== null) {
            $dataSystem = $this->starSystemRepository->find($dataSystemId);

            if ($dataSystem !== null) {
                $cx = $dataSystem->getCx();
                if ($cx !== null) {
                    return $cx;
                }
            }
        }

        $posX = $data->getPosX();

        return $posX;
    }
    private function getRelevantDataYCoordinate(SpacecraftCountData $data): int
    {
        $dataSystemId = $data->getSystemId();

        if ($dataSystemId !== null) {
            $dataSystem = $this->starSystemRepository->find($dataSystemId);
            if ($dataSystem !== null) {
                $cy = $dataSystem->getCy();
                if ($cy !== null) {
                    return $cy;
                }
            }
        }

        return $data->getPosY();
    }
}
