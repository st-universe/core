<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowSubspaceTelescope;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;

final class ShowSubspaceTelescope implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SUBSPACE_TELESCOPE';


    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyGuiHelperInterface $colonyGuiHelper,
        private StarmapUiFactoryInterface $starmapUiFactory,
        private ColonyFunctionManagerInterface $colonyFunctionManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId,
            false
        );

        if (!$this->colonyFunctionManager->hasFunction($colony, BuildingFunctionEnum::SUBSPACE_TELESCOPE)) {
            return;
        }

        $this->colonyGuiHelper->registerMenuComponents(ColonyMenuEnum::MENU_SUBSPACE_TELESCOPE, $colony, $game);

        $game->showMacro(ColonyMenuEnum::MENU_SUBSPACE_TELESCOPE->getTemplate());

        $system = $colony->getSystem();
        $mapX =  (int) ceil($system->getCx() / MapEnum::FIELDS_PER_SECTION);
        $mapY =  (int) ceil($system->getCy() / MapEnum::FIELDS_PER_SECTION);
        $layer = $system->getLayer();

        $game->addExecuteJS(sprintf(
            "registerNavKeys('%s.php', '%s', '%s');",
            ModuleEnum::STARMAP->value,
            RefreshSection::VIEW_IDENTIFIER,
            'html/colony/telescopeSectionTable.twig'
        ), GameEnum::JS_EXECUTION_AJAX_UPDATE);

        $game->addExecuteJS(
            sprintf(
                'setColonyMapCoordinates(%d, %d);',
                $system->getCx(),
                $system->getCy()
            ),
            GameEnum::JS_EXECUTION_AJAX_UPDATE
        );

        $helper = $this->starmapUiFactory->createMapSectionHelper();
        $helper->setTemplateVars(
            $game,
            $layer,
            $mapX + ($mapY - 1) * ((int) ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION)),
        );
    }
}
