<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui;

use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\VisualPanelElementInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\Colony;

class ColonyScanPanel extends AbstractVisualPanel
{
    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private Colony $colony,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        $range = 1;

        if ($this->colonyFunctionManager->hasFunction($this->colony, BuildingFunctionEnum::SUBSPACE_TELESCOPE)) {
            $range = 3;
        } elseif ($this->colonyFunctionManager->hasFunction($this->colony, BuildingFunctionEnum::COLONY_CENTRAL)) {
            $range = 2;
        }

        return PanelBoundaries::fromLocation($this->colony->getStarsystemMap(), $range);
    }

    #[Override]
    protected function loadLayers(): void
    {
        $showCloaked = $this->colonyFunctionManager->hasFunction($this->colony, BuildingFunctionEnum::COLONY_CENTRAL);

        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer($showCloaked, null, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addSystemLayer()
            ->addAnomalyLayer()
            ->addColonyShieldLayer();

        if ($this->colonyFunctionManager->hasFunction($this->colony, BuildingFunctionEnum::SUBSPACE_TELESCOPE)) {
            $panelLayerCreation->addSubspaceLayer($this->colony->getUser()->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }
        if ($this->colonyFunctionManager->hasFunction($this->colony, BuildingFunctionEnum::SUBSPACE_TELESCOPE)) {
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
