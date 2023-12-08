<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class BuildmenuProvider implements GuiComponentProviderInterface
{
    private BuildingRepositoryInterface $buildingRepository;

    public function __construct(
        BuildingRepositoryInterface $buildingRepository,
    ) {
        $this->buildingRepository = $buildingRepository;
    }

    /** @param ColonyInterface&PlanetFieldHostInterface $host */
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {


        foreach (BuildMenuEnum::BUILDMENU_IDS as $id) {

            $buildings = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                $host,
                $game->getUser()->getId(),
                $id,
                0
            );

            if (request::has('cid')) {
                $buildings = array_filter(
                    $buildings,
                    fn (BuildingInterface $building) => $building->getCommodities()->containsKey(request::getIntFatal('cid'))
                );
            }

            $menus[$id]['buildings'] = $buildings;
            $menus[$id]['name'] = BuildMenuEnum::getDescription($id);
        }

        $game->setTemplateVar('BUILD_MENUS', $menus);
    }
}
