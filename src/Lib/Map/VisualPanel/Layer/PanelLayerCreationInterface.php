<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;

interface PanelLayerCreationInterface
{
    public function addSystemLayer(): PanelLayerCreationInterface;

    public function addMapLayer(LayerInterface $layer): PanelLayerCreationInterface;

    public function addColonyShieldLayer(): PanelLayerCreationInterface;

    public function addSubspaceLayer(int $id, SubspaceLayerTypeEnum $type): PanelLayerCreationInterface;

    public function addBorderLayer(?ShipInterface $currentShip, ?bool $isOnShipLevel): PanelLayerCreationInterface;

    public function addShipCountLayer(bool $showCloakedEverywhere, ?ShipInterface $currentShip): PanelLayerCreationInterface;

    public function build(AbstractVisualPanel $panel): PanelLayers;
}
