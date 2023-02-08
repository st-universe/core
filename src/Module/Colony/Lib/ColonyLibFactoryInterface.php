<?php

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

interface ColonyLibFactoryInterface
{

    public function createBuildingFunctionTal(
        array $buildingFunctionIds
    ): BuildingFunctionTalInterface;

    public function createColonySurface(
        ColonyInterface $colony,
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface;

    public function createBuildableRumpItem(
        ShipRumpInterface $shipRump,
        UserInterface $currentUser
    ): BuildableRumpListItemInterface;

    /**
     * @param array<ColonyProduction> $production
     */
    public function createColonyProductionPreviewWrapper(
        array $production
    ): ColonyProductionPreviewWrapper;

    public function createEpsProductionPreviewWrapper(
        ColonyInterface $colony
    ): ColonyEpsProductionPreviewWrapper;
}
