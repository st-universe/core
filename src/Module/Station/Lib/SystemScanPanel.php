<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\SystemScanPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\User;

class SystemScanPanel extends AbstractVisualPanel
{
    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private SpacecraftWrapperInterface $currentWrapper,
        private StarSystem $system,
        private User $user,
        LoggerUtilInterface $loggerUtil,
        private bool $tachyonFresh = false
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[\Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromSystem($this->system);
    }

    #[\Override]
    protected function loadLayers(): void
    {
        $currentSpacecraft = $this->currentWrapper->get();

        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer($this->tachyonFresh, $currentSpacecraft, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($this->currentWrapper, $this->system === $currentSpacecraft->getSystem())
            ->addAnomalyLayer()
            ->addSystemLayer()
            ->addColonyShieldLayer();

        if ($currentSpacecraft->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($this->user->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[\Override]
    protected function getEntryCallable(): callable
    {
        return fn(int $x, int $y): SystemScanPanelEntry => new SystemScanPanelEntry(
            $x,
            $y,
            $this->layers,
            $this->system,
        );
    }

    #[\Override]
    protected function getPanelViewportPercentage(): int
    {
        return 33;
    }
}
