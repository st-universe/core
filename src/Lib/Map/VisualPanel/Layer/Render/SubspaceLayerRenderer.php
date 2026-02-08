<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Stu\Lib\Map\VisualPanel\Layer\Data\AbstractSubspaceData;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

final class SubspaceLayerRenderer implements LayerRendererInterface
{
    public function __construct(
        private readonly int $zIndex,
        private readonly bool $isInverted = false
    ) {}

    /** @param AbstractSubspaceData $data */
    #[\Override]
    public function render(CellDataInterface $data, PanelAttributesInterface $panel): string
    {
        if ($data->isDisabled()) {
            return '';
        }

        $subspaceCode = $this->getSubspaceCode($data);
        if ($subspaceCode === null) {
            return '';
        }

        return sprintf(
            '<img src="/assets/subspace/generated/%s.png" class="visualPanelLayer%s"
                style="z-index: %d; %s" />',
            $subspaceCode,
            $this->isInverted ? ' inverted' : '',
            $this->zIndex,
            $panel->getHeightAndWidth()
        );
    }

    private function getSubspaceCode(AbstractSubspaceData $data): ?string
    {
        if (!$this->isSubspaceCodeAvailable($data)) {
            return null;
        }

        return sprintf(
            '%d%d%d%d',
            $this->getCode($data->getDirection1Count()),
            $this->getCode($data->getDirection2Count()),
            $this->getCode($data->getDirection3Count()),
            $this->getCode($data->getDirection4Count())
        );
    }

    private function isSubspaceCodeAvailable(AbstractSubspaceData $data): bool
    {
        return $data->getDirection1Count() > 0
            || $data->getDirection2Count() > 0
            || $data->getDirection3Count() > 0
            || $data->getDirection4Count() > 0;
    }

    private function getCode(int $shipCount): int
    {
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
}
