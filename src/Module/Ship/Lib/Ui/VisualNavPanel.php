<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Override;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Lib\Map\Location;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Shipcount\ShipcountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel extends AbstractVisualPanel
{
    private ?Location $panelCenter = null;

    private ?bool $isOnShipLevel = null;

    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private UserMapRepositoryInterface $userMapRepository,
        private ShipInterface $currentShip,
        private UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        private bool $tachyonFresh
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromLocation($this->getPanelCenter(), $this->currentShip->getSensorRange());
    }

    #[Override]
    protected function loadLayers(): void
    {

        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer($this->tachyonFresh, $this->currentShip, ShipcountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($this->currentShip, $this->isOnShipLevel());

        $map = $this->getPanelCenter()->get();

        if ($map instanceof MapInterface) {
            $panelLayerCreation->addMapLayer($map->getLayer());
            $this->createUserMapEntries();
        } else {
            $panelLayerCreation
                ->addSystemLayer()
                ->addColonyShieldLayer();
        }

        if ($this->currentShip->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($this->user->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn (int $x, int $y): VisualNavPanelEntry => new VisualNavPanelEntry(
            $x,
            $y,
            $this->isOnShipLevel(),
            $this->layers,
            $this->currentShip
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return $this->currentShip->isBase() ? 50 : 33;
    }

    private function isOnShipLevel(): bool
    {
        if ($this->isOnShipLevel === null) {
            $this->isOnShipLevel = $this->currentShip->getLocation()->get() === $this->getPanelCenter()->get();
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
        $location = $this->currentShip->getLocation();
        if ($location->isMap()) {
            return $location;
        }

        if (
            $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            $parentMapLocation = $location->getParentMapLocation();

            return $parentMapLocation ?? $location;
        }

        return $location;
    }

    private function createUserMapEntries(): void
    {
        $map = $this->currentShip->getMap();
        if ($map === null) {
            return;
        }

        $cx = $map->getCx();
        $cy = $map->getCy();
        $layerId = $map->getLayer()->getId();
        $range = $this->currentShip->getSensorRange();

        if ($this->isUserMapActive($layerId)) {
            $this->userMapRepository->insertMapFieldsForUser(
                $this->user->getId(),
                $layerId,
                $cx,
                $cy,
                $range
            );
        }
    }

    private function isUserMapActive(int $layerId): bool
    {
        if (!$this->user->hasColony()) {
            return false;
        }

        return !$this->user->hasExplored($layerId);
    }
}
