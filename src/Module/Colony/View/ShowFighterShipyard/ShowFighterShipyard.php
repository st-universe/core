<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowFighterShipyard;

use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowFighterShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIGHTER_SHIPYARD';

    private ColonyLoaderInterface $colonyLoader;

    private ShowFighterShipyardRequestInterface $showFighterShipyardRequest;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowFighterShipyardRequestInterface $showFighterShipyardRequest,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showFighterShipyardRequest = $showFighterShipyardRequest;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFighterShipyardRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro(ColonyMenuEnum::MENU_FIGHTER_SHIPYARD->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_FIGHTER_SHIPYARD);

        $game->setTemplateVar('COLONY', $colony);

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
                $userId,
                BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD
            )
        );
    }
}
