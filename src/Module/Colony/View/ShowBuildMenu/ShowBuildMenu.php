<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenu;

use Stu\Component\Building\BuildMenuEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\BuildMenuWrapper;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ShowBuildMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowBuildMenuRequestInterface $showBuildMenuRequest;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildMenuRequestInterface $showBuildMenuRequest,
        BuildingRepositoryInterface $buildingRepository,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildMenuRequest = $showBuildMenuRequest;
        $this->buildingRepository = $buildingRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildMenuRequest->getColonyId(),
            $userId,
            false
        );

        $colonyId = $colony->getId();

        $this->colonyGuiHelper->register($colony, $game);

        $menus = [];

        foreach (BuildMenuEnum::BUILDMENU_IDS as $id) {
            $menus[$id]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                $colonyId,
                $userId,
                $id,
                0
            );
        }

        $game->showMacro('html/colonymacros.xhtml/cm_buildmenu');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_BUILD));
        $game->setTemplateVar('BUILD_MENUS', $menus);
        $game->setTemplateVar('BUILD_MENU', new BuildMenuWrapper());
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
        $game->setTemplateVar(
            'SHIELDING_MANAGER',
            $this->colonyLibFactory->createColonyShieldingManager($colony)
        );
    }
}
