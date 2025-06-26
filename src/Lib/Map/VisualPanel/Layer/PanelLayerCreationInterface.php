<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Spacecraft;

interface PanelLayerCreationInterface
{
    public function addSystemLayer(): PanelLayerCreationInterface;

    public function addMapLayer(Layer $layer): PanelLayerCreationInterface;

    public function addColonyShieldLayer(): PanelLayerCreationInterface;

    public function addSubspaceLayer(int $id, SubspaceLayerTypeEnum $type): PanelLayerCreationInterface;

    public function addBorderLayer(?SpacecraftWrapperInterface $currentWrapper, ?bool $isOnShipLevel): PanelLayerCreationInterface;

    public function addAnomalyLayer(): PanelLayerCreationInterface;

    public function addShipCountLayer(
        bool $showCloakedEverywhere,
        ?Spacecraft $currentSpacecraft,
        SpacecraftCountLayerTypeEnum $type,
        int $id
    ): PanelLayerCreationInterface;

    public function build(AbstractVisualPanel $panel): PanelLayers;
}
