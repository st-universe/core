<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel extends AbstractVisualPanel
{
    private LocationInterface|null $panelCenter = null;

    private ?bool $isOnShipLevel = null;

    public function __construct(
        PanelLayerCreationInterface $panelLayerCreation,
        private UserMapRepositoryInterface $userMapRepository,
        private SpacecraftInterface $currentSpacecraft,
        private UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        private bool $tachyonFresh
    ) {
        parent::__construct($panelLayerCreation, $loggerUtil);
    }

    #[Override]
    protected function createBoundaries(): PanelBoundaries
    {
        return PanelBoundaries::fromLocation($this->getPanelCenter(), $this->currentSpacecraft->getSensorRange());
    }

    #[Override]
    protected function loadLayers(): void
    {
        $panelLayerCreation = $this->panelLayerCreation
            ->addShipCountLayer($this->tachyonFresh, $this->currentSpacecraft, SpacecraftCountLayerTypeEnum::ALL, 0)
            ->addBorderLayer($this->currentSpacecraft, $this->isOnShipLevel())
            ->addAnomalyLayer();

        $map = $this->getPanelCenter();

        if ($map instanceof MapInterface) {
            $layer = $map->getLayer();
            if ($layer === null) {
                throw new RuntimeException('this should not happen');
            }
            $panelLayerCreation->addMapLayer($layer);
            $this->createUserMapEntries($layer);
        } else {
            $panelLayerCreation
                ->addSystemLayer()
                ->addColonyShieldLayer();
        }

        if ($this->currentSpacecraft->getSubspaceState()) {
            $panelLayerCreation->addSubspaceLayer($this->user->getId(), SubspaceLayerTypeEnum::IGNORE_USER);
        }

        $this->layers = $panelLayerCreation->build($this);
    }

    #[Override]
    protected function getEntryCallable(): callable
    {
        return fn(int $x, int $y): VisualNavPanelEntry => new VisualNavPanelEntry(
            $x,
            $y,
            $this->isOnShipLevel(),
            $this->layers,
            $this->currentSpacecraft
        );
    }

    #[Override]
    protected function getPanelViewportPercentage(): int
    {
        return $this->currentSpacecraft->isStation() ? 50 : 33;
    }

    private function isOnShipLevel(): bool
    {
        if ($this->isOnShipLevel === null) {
            $this->isOnShipLevel = $this->currentSpacecraft->getLocation() === $this->getPanelCenter();
        }

        return $this->isOnShipLevel;
    }

    private function getPanelCenter(): LocationInterface
    {
        if ($this->panelCenter === null) {
            $this->panelCenter = $this->determinePanelCenter();
        }

        return $this->panelCenter;
    }

    private function determinePanelCenter(): LocationInterface
    {
        $location = $this->currentSpacecraft->getLocation();
        if ($location instanceof MapInterface) {
            return $location;
        }

        if (
            $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpEnum::SHIP_ROLE_SENSOR
            || $this->currentSpacecraft->getRump()->getRoleId() === SpacecraftRumpEnum::SHIP_ROLE_BASE
        ) {
            $parentMapLocation = $this->getParentMapLocation($location);

            return $parentMapLocation ?? $location;
        }

        return $location;
    }

    private function getParentMapLocation(LocationInterface $location): ?MapInterface
    {
        if ($location instanceof StarSystemMapInterface) {
            return $location->getSystem()->getMap();
        }

        return null;
    }

    private function createUserMapEntries(LayerInterface $layer): void
    {
        $map = $this->currentSpacecraft->getMap();
        if ($map === null) {
            return;
        }

        $cx = $map->getX();
        $cy = $map->getY();
        $range = $this->currentSpacecraft->getSensorRange();

        if ($this->isUserMapActive($layer->getId())) {
            $this->userMapRepository->insertMapFieldsForUser(
                $this->user->getId(),
                $layer->getId(),
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
