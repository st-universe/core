<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use ColonyMenu;
use Shiprump;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowFighterShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIGHTER_SHIPYARD';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showFighterShipyardRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowFighterShipyardRequestInterface $showFighterShipyardRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showFighterShipyardRequest = $showFighterShipyardRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFighterShipyardRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_fighter_shipyard');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_FIGHTER_SHIPYARD));

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            Shiprump::getBuildableRumpsByBuildingFunction($userId, BUILDING_FUNCTION_FIGHTER_SHIPYARD)
        );
    }
}
