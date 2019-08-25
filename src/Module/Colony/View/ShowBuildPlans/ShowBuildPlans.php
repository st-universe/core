<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use BuildingFunctions;
use ColonyMenu;
use request;
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

        $this->colonyGuiHelper->register($colony, $game);

        $buildingFunction = new BuildingFunctions(request::getIntFatal('func'));

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
