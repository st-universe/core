<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescope;

use ColonyMenu;
use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\RefreshSubspaceSection\RefreshSubspaceSection;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\View\Overview\Overview;

final class ShowSubspaceTelescope implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SUBSPACE_TELESCOPE';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$colony->hasActiveBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            return;
        }

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_telescope');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_SUBSPACE_TELESCOPE));

        $mapX =  (int) ceil($colony->getSystem()->getCx() / Overview::FIELDS_PER_SECTION);
        $mapY =  (int) ceil($colony->getSystem()->getCy() / Overview::FIELDS_PER_SECTION);

        $helper = new MapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $mapX,
            $mapY,
            $mapX + ($mapY - 1) * ((int) ceil(MapEnum::MAP_MAX_X / Overview::FIELDS_PER_SECTION)),
            ModuleViewEnum::MODULE_VIEW_COLONY,
            RefreshSubspaceSection::VIEW_IDENTIFIER
        );
    }
}
