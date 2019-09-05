<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use ColonyMenu;
use Shiprump;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;

final class ShowShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPYARD';

    private $colonyLoader;

    private $showShipyardRequest;

    private $buildingFunctionRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipyardRequestInterface $showShipyardRequest,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipyardRequest = $showShipyardRequest;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
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
        $buildableShips = Shiprump::getBuildableRumpsByBuildingFunction($userId, $function->getFunction());

        $game->showMacro('html/colonymacros.xhtml/cm_shipyard');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_SHIPYARD));

        $game->setTemplateVar('BUILDABLE_SHIPS', $buildableShips);
        $game->setTemplateVar('BUILDABLE_RUMPS', Shiprump::getBuildableRumpsByUser($userId));
    }
}
