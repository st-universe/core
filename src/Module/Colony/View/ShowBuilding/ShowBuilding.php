<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Building;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;

final class ShowBuilding implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDING';

    private $colonyLoader;

    private $showBuildingRequest;

    private $buildingFieldAlternativeRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBuildingRequestInterface $showBuildingRequest,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBuildingRequest = $showBuildingRequest;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildingRequest->getColonyId(),
            $userId
        );

        $building = new Building($this->showBuildingRequest->getBuildingId());

        $alternativeBuildings = $this->buildingFieldAlternativeRepository->getByBuildingId(
            (int) $building->getId()
        );

        $game->setPageTitle($building->getName());
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/buildinginfo');
        $game->setTemplateVar('buildingdata', $building);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('ALTERNATIVE_BUILDINGS', $alternativeBuildings);
        $game->setTemplateVar(
            'SINGLE_ALTERNATIVE_BUILDING',
            $alternativeBuildings === [] ? null :  current($alternativeBuildings)
        );
    }
}
