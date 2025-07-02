<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Override;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

class VisualNavPanel extends AbstractVisualPanel
{
    private Location|null $panelCenter = null;

    private ?bool $isOnShipLevel = null;

    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private PanelLayerConfiguration $panelLayerConfiguration,
        private SpacecraftWrapperInterface $wrapper,
        private User $user,
        LoggerUtilInterface $loggerUtil,
        private bool $tachyonFresh
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        $range = $this->wrapper->getLssSystemData()?->getSensorRange() ?? 0;

        return PanelBoundaries::fromLocation($this->getPanelCenter(), $range);
    }

    #[Override]
    protected function loadLayers(): void
    {
        $this->panelLayerConfiguration->configureLayers(
            $this->panelLayerCreation,
            $this->wrapper,
            $this->getPanelCenter(),
            $this->user,
            $this->tachyonFresh,
            $this->isOnShipLevel()
        );

        $this->layers = $this->panelLayerCreation->build($this, $this->getPanelCenter());
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn(int $x, int $y): VisualNavPanelEntry => new VisualNavPanelEntry(
            $x,
            $y,
            $this->isOnShipLevel(),
            $this->layers,
            $this->wrapper->get()
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return $this->wrapper->get()->isStation() ? 50 : 33;
    }

    private function isOnShipLevel(): bool
    {
        if ($this->isOnShipLevel === null) {
            $this->isOnShipLevel = $this->wrapper->get()->getLocation() === $this->getPanelCenter();
        }

        return $this->isOnShipLevel;
    }

    private function getPanelCenter(): Location
    {
        if ($this->panelCenter === null) {
            $this->panelCenter = $this->determinePanelCenter();
        }

        return $this->panelCenter;
    }

    private function determinePanelCenter(): Location
    {
        $location = $this->wrapper->get()->getLocation();
        if ($location instanceof Map) {
            return $location;
        }

        if (
            $this->wrapper->get()->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR
            || $this->wrapper->get()->getRump()->getRoleId() === SpacecraftRumpRoleEnum::SHIP_ROLE_BASE
        ) {
            $parentMapLocation = $this->getParentMapLocation($location);

            return $parentMapLocation ?? $location;
        }

        return $location;
    }

    private function getParentMapLocation(Location $location): ?Map
    {
        if ($location instanceof StarSystemMap) {
            return $location->getSystem()->getMap();
        }

        return null;
    }
}
