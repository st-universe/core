<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

use Stu\Component\Building\BuildMenuEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ShowBuildMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private BuildingRepositoryInterface $buildingRepository;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyGuiHelperInterface $colonyGuiHelper,
        BuildingRepositoryInterface $buildingRepository,
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $this->colonyGuiHelper->registerComponents($host, $game);
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_BUILD);

        $menus = [];

        foreach (BuildMenuEnum::BUILDMENU_IDS as $id) {
            $menus[$id]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                $host,
                $userId,
                $id,
                0
            );

            $menus[$id]['name'] = BuildMenuEnum::getDescription($id);
        }

        $game->showMacro(ColonyMenuEnum::MENU_BUILD->getTemplate());

        $game->setTemplateVar('BUILD_MENUS', $menus);
    }
}
