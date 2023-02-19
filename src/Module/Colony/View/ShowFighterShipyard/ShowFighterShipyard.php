<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowFighterShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIGHTER_SHIPYARD';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowFighterShipyardRequestInterface $showFighterShipyardRequest;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowFighterShipyardRequestInterface $showFighterShipyardRequest,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showFighterShipyardRequest = $showFighterShipyardRequest;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFighterShipyardRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_fighter_shipyard');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_FIGHTER_SHIPYARD));

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->shipRumpRepository->getBuildableByUserAndBuildingFunction($userId,
                BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD)
        );
    }
}
