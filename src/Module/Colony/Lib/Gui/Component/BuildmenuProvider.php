<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Stu\Component\Building\BuildMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
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
            $menus[$id]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                $host,
                $game->getUser()->getId(),
                $id,
                0
            );

            $menus[$id]['name'] = BuildMenuEnum::getDescription($id);
        }

        $game->setTemplateVar('BUILD_MENUS', $menus);
    }
}
