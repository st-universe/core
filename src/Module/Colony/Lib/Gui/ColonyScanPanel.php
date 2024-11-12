<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use Override;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\VisualPanelElementInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;

class ColonyScanPanel extends AbstractVisualPanel
{
    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private ColonyInterface $colony,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        $range = 1;
        // TODO range = 2 for colony central

        if ($this->colonyFunctionManager->hasFunction($this->colony, BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            $range = 3;
        }

        return PanelBoundaries::fromLocation($this->colony->getStarsystemMap(), $range);
    }

    #[Override]
    protected function loadLayers(): void
    {
        //TODO cloaked for colony central
        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer(false, null, ShipcountLayerTypeEnum::ALL, 0)
            ->addSystemLayer()
            ->addColonyShieldLayer();

        if ($this->colonyFunctionManager->hasFunction($this->colony, BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            $panelLayerCreation->addSubspaceLayer($this->colony->getUser()->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn(int $x, int $y): VisualPanelElementInterface => new ColonyScanPanelEntry(
            $x,
            $y,
            $this->layers
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return 8;
    }
}
