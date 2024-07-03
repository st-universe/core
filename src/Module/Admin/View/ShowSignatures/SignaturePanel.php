<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;

class SignaturePanel extends AbstractVisualPanel
{
    /** @param array{minx: int, maxx: int, miny: int, maxy: int} $data */
    public function __construct(
        private array $data,
        PanelLayerCreationInterface $panelLayerCreation,
        private LayerInterface $layer,
        private int $shipId,
        private int $userId,
        private int $allyId,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromArray($this->data, $this->layer);
    }

    #[Override]
    protected function loadLayers(): void
    {

        $panelLayerCreation = $this->panelLayerCreation
            ->addBorderLayer(null, null);

        $panelLayerCreation->addMapLayer($this->layer);

        if ($this->shipId !== 0) {
            $panelLayerCreation->addShipCountLayer(true, null, ShipcountLayerTypeEnum::SHIP_ONLY, $this->shipId);
            $panelLayerCreation->addSubspaceLayer($this->shipId, SubspaceLayerTypeEnum::SHIP_ONLY);
        } elseif ($this->userId !== 0) {
            $panelLayerCreation->addShipCountLayer(true, null, ShipcountLayerTypeEnum::USER_ONLY, $this->userId);
            $panelLayerCreation->addSubspaceLayer($this->userId, SubspaceLayerTypeEnum::USER_ONLY);
        } elseif ($this->allyId !== 0) {
            $panelLayerCreation->addShipCountLayer(true, null, ShipcountLayerTypeEnum::ALLIANCE_ONLY, $this->allyId);
            $panelLayerCreation->addSubspaceLayer($this->allyId, SubspaceLayerTypeEnum::ALLIANCE_ONLY);
        } else {
            $panelLayerCreation->addShipCountLayer(true, null, ShipcountLayerTypeEnum::ALL, 0);
            $panelLayerCreation->addSubspaceLayer(0, SubspaceLayerTypeEnum::ALL);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn (int $x, int $y): SignaturePanelEntry => new SignaturePanelEntry(
            $x,
            $y,
            $this->layers
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return 100;
    }
}
