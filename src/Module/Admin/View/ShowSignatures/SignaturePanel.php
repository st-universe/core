<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;

class SignaturePanel extends AbstractVisualPanel
{
    private int $userId;
    private int $allyId;

    /** @var array{minx: int, maxx: int, miny: int, maxy: int} */
    private array $data;

    private LayerInterface $layer;

    /** @param array{minx: int, maxx: int, miny: int, maxy: int} $data */
    public function __construct(
        array $data,
        PanelLayerCreationInterface $panelLayerCreation,
        LayerInterface $layer,
        int $userId,
        int $allyId,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);

        $this->data = $data;
        $this->layer = $layer;
        $this->userId = $userId;
        $this->allyId = $allyId;
    }

    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromArray($this->data, $this->layer);
    }

    protected function loadLayers(): void
    {

        $panelLayerCreation = $this->panelLayerCreation
            //TODO shipCountLayerFactory...
            //->addShipCountLayer($this->isTachyonSystemActive, $this->tachyonFresh, $this->currentShip)
            ->addBorderLayer(null, null);

        $panelLayerCreation->addMapLayer($this->layer);

        if ($this->userId !== 0) {
            $panelLayerCreation->addSubspaceLayer($this->userId, SubspaceLayerTypeEnum::USER_ONLY);
        } elseif ($this->allyId !== 0) {
            $panelLayerCreation->addSubspaceLayer($this->allyId, SubspaceLayerTypeEnum::ALLIANCE_ONLY);
        } else {
            $panelLayerCreation->addSubspaceLayer(0, SubspaceLayerTypeEnum::ALL);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    protected function getEntryCallable(): callable
    {
        return fn (int $x, int $y) => new SignaturePanelEntry(
            $x,
            $y,
            $this->layers
        );
    }

    protected function getPanelViewportPercentage(): int
    {
        return 100;
    }
}
