<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingManagementProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private CommodityRepositoryInterface $commodityRepository) {}

    #[\Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $list = $this->planetFieldRepository->getByColonyWithBuilding($entity);

        $game->setTemplateVar('PLANET_FIELD_LIST', $list);
        $game->setTemplateVar('USEABLE_COMMODITY_LIST', $this->commodityRepository->getByBuildingsOnColony($entity));
    }
}
