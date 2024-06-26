<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

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
    private ShipInterface $currentShip;

    private UserInterface $user;

    private bool $tachyonFresh;

    private UserMapRepositoryInterface $userMapRepository;

    private ?Location $panelCenter = null;

    private ?bool $isOnShipLevel = null;

    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        UserMapRepositoryInterface $userMapRepository,
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $tachyonFresh
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);

        $this->userMapRepository = $userMapRepository;
        $this->currentShip = $currentShip;
        $this->user = $user;
        $this->tachyonFresh = $tachyonFresh;
    }

    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromLocation($this->getPanelCenter(), $this->currentShip->getSensorRange());
    }

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

    protected function getEntryCallable(): callable
    {
        return fn (int $x, int $y) => new VisualNavPanelEntry(
            $x,
            $y,
            $this->isOnShipLevel(),
            $this->layers,
            $this->currentShip
        );
    }

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
        $map = $this->currentShip->getCurrentMapField();
        if ($map instanceof MapInterface) {
            return new Location($map, null);
        }

        if (
            $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentShip->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_BASE
        ) {
            $mapOfSystem = $map->getSystem()->getMapField();

            return $mapOfSystem === null ? new Location(null, $map) : new Location($mapOfSystem, null);
        }

        return new Location(null, $map);
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
