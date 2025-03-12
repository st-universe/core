<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SystemScanPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

class SystemScanPanel extends AbstractVisualPanel
{
    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private SpacecraftInterface $currentSpacecraft,
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
            ->addShipCountLayer($this->currentSpacecraft->getTachyonState(), null, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($this->currentSpacecraft, $this->system === $this->currentSpacecraft->getSystem())
            ->addAnomalyLayer()
            ->addSystemLayer()
            ->addColonyShieldLayer();

        if ($this->currentSpacecraft->getSubspaceState()) {
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
