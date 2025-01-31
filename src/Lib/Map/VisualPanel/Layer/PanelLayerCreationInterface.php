<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface PanelLayerCreationInterface
{
    public function addSystemLayer(): PanelLayerCreationInterface;

    public function addMapLayer(LayerInterface $layer): PanelLayerCreationInterface;

    public function addColonyShieldLayer(): PanelLayerCreationInterface;

    public function addSubspaceLayer(int $id, SubspaceLayerTypeEnum $type): PanelLayerCreationInterface;

    public function addBorderLayer(?SpacecraftInterface $currentSpacecraft, ?bool $isOnShipLevel): PanelLayerCreationInterface;

    public function addAnomalyLayer(): PanelLayerCreationInterface;

    public function addShipCountLayer(
        bool $showCloakedEverywhere,
        ?SpacecraftInterface $currentSpacecraft,
        SpacecraftCountLayerTypeEnum $type,
        int $id
    ): PanelLayerCreationInterface;

    public function build(AbstractVisualPanel $panel): PanelLayers;
}
