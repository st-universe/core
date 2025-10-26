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
    public const string VIEW_IDENTIFIER = 'SHOW_BUILDMENU_PART';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingRepositoryInterface $buildingRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $menus = [];

        foreach (BuildMenuEnum::cases() as $menu) {
            $menus[$menu->value]['buildings'] = $this->buildingRepository->getBuildmenuBuildings(
                $host,
                $userId,
                $menu,
                0
            );
        }

        $game->showMacro('html/colony/component/buildmenu.twig');
    }
}
