<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingManagementProvider implements GuiComponentProviderInterface
{
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $list = $this->planetFieldRepository->getByColonyWithBuilding($host);

        $game->setTemplateVar('PLANET_FIELD_LIST', $list);
        $game->setTemplateVar('USEABLE_COMMODITY_LIST', $this->commodityRepository->getByBuildingsOnColony($host));
    }
}
