<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescope;

use Stu\Module\Colony\Lib\ColonyMenu;
use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\RefreshSubspaceSection\RefreshSubspaceSection;
use Stu\Module\Starmap\Lib\MapSectionHelper;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;

final class ShowSubspaceTelescope implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SUBSPACE_TELESCOPE';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private StarmapUiFactoryInterface $starmapUiFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        StarmapUiFactoryInterface $starmapUiFactory,
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->starmapUiFactory = $starmapUiFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId,
            false
        );

        if (!$this->colonyFunctionManager->hasFunction($colony, BuildingEnum::BUILDING_FUNCTION_SUBSPACE_TELESCOPE)) {
            return;
        }

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_telescope');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_SUBSPACE_TELESCOPE));

        $mapX =  (int) ceil($colony->getSystem()->getCx() / MapEnum::FIELDS_PER_SECTION);
        $mapY =  (int) ceil($colony->getSystem()->getCy() / MapEnum::FIELDS_PER_SECTION);
        $layer = $colony->getSystem()->getLayer();

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $mapX,
            $mapY,
            $mapX + ($mapY - 1) * ((int) ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION)),
            ModuleViewEnum::MODULE_VIEW_COLONY,
            RefreshSubspaceSection::VIEW_IDENTIFIER
        );
    }
}
