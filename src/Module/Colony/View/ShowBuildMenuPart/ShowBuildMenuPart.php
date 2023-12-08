<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenuPart;

use Stu\Component\Building\BuildMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ShowBuildMenuPart implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU_PART';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private BuildingRepositoryInterface $buildingRepository;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $menus = [];

        foreach (BuildMenuEnum::BUILDMENU_IDS as $id) {
            $menus[$id]['buildings'] = $this->buildingRepository->getBuildmenuBuildings(
                $host,
                $userId,
                $id,
                0
            );
        }

        $game->showMacro('html/colony/component/buildmenu.twig');
    }
}
