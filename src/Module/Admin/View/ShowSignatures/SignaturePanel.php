<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\Layer;

class SignaturePanel extends AbstractVisualPanel
{
    /** @param array{minx: int, maxx: int, miny: int, maxy: int} $data */
    public function __construct(
        private array $data,
        PanelLayerCreationInterface $panelLayerCreation,
        private Layer $layer,
        private int $shipId,
        private int $userId,
        private int $allyId,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[\Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromArray($this->data, $this->layer);
    }

    #[\Override]
    protected function loadLayers(): void
    {

        $panelLayerCreation = $this->panelLayerCreation
            ->addBorderLayer(null, null)
            ->addMapLayer($this->layer);

        if ($this->shipId !== 0) {
            $panelLayerCreation
                ->addShipCountLayer(true, null, SpacecraftCountLayerTypeEnum::SPACECRAFT_ONLY, $this->shipId)
                ->addSubspaceLayer($this->shipId, SubspaceLayerTypeEnum::SPACECRAFT_ONLY);
        } elseif ($this->userId !== 0) {
            $panelLayerCreation
                ->addShipCountLayer(true, null, SpacecraftCountLayerTypeEnum::USER_ONLY, $this->userId)
                ->addSubspaceLayer($this->userId, SubspaceLayerTypeEnum::USER_ONLY);
        } elseif ($this->allyId !== 0) {
            $panelLayerCreation
                ->addShipCountLayer(true, null, SpacecraftCountLayerTypeEnum::ALLIANCE_ONLY, $this->allyId)
                ->addSubspaceLayer($this->allyId, SubspaceLayerTypeEnum::ALLIANCE_ONLY);
        } else {
            $panelLayerCreation
                ->addShipCountLayer(true, null, SpacecraftCountLayerTypeEnum::ALL, 0)
                ->addSubspaceLayer(0, SubspaceLayerTypeEnum::ALL);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[\Override]
    protected function getEntryCallable(): callable
    {
        return fn (int $x, int $y): SignaturePanelEntry => new SignaturePanelEntry(
            $x,
            $y,
            $this->layers
        );
    }

    #[\Override]
    protected function getPanelViewportPercentage(): int
    {
        return 100;
    }
}
