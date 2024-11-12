<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SystemScanPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

class SystemScanPanel extends AbstractVisualPanel
{
    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private ShipInterface $currentShip,
        private StarSystemInterface $system,
        private UserInterface $user,
        LoggerUtilInterface $loggerUtil
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromSystem($this->system);
    }

    #[Override]
    protected function loadLayers(): void
    {
        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer($this->currentShip->getTachyonState(), null, ShipcountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($this->currentShip, $this->system === $this->currentShip->getSystem())
            ->addSystemLayer()
            ->addColonyShieldLayer();

        if ($this->currentShip->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($this->user->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn(int $x, int $y): SystemScanPanelEntry => new SystemScanPanelEntry(
            $x,
            $y,
            $this->layers,
            $this->system,
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return 33;
    }
}
