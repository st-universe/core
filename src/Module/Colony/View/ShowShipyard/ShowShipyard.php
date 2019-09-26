<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use ColonyMenu;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPYARD';

    private $colonyLoader;

    private $showShipyardRequest;

    private $buildingFunctionRepository;

    private $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipyardRequestInterface $showShipyardRequest,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipyardRequest = $showShipyardRequest;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipyardRequest->getColonyId(),
            $userId
        );

        $function = $this->buildingFunctionRepository->find(
            $this->showShipyardRequest->getBuildingFunctionId()
        );

        $game->showMacro('html/colonymacros.xhtml/cm_shipyard');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_SHIPYARD));

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            $this->shipRumpRepository->getBuildableByUserAndBuildingFunction($userId, $function->getFunction())
        );
        $game->setTemplateVar('BUILDABLE_RUMPS', $this->shipRumpRepository->getBuildableByUser($userId));
    }
}
