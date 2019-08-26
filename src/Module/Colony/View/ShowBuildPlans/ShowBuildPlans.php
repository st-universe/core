<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use BuildingFunctions;
use ColonyMenu;
use ShipBuildplans;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowBuildPlans implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDPLANS';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showBuildPlansRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildPlansRequestInterface $showBuildPlansRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildPlansRequest = $showBuildPlansRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildPlansRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $buildingFunction = new BuildingFunctions($this->showBuildPlansRequest->getBuildingFunctionId());

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_buildplans');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_BUILDPLANS));
        $game->setTemplateVar(
            'AVAILABLE_BUILDPLANS',
            ShipBuildplans::getBuildplansByUserAndFunction($userId, $buildingFunction->getFunction())
        );
    }
}
