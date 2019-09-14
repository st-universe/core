<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ShowBuilding implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDING';

    private $colonyLoader;

    private $showBuildingRequest;

    private $buildingFieldAlternativeRepository;

    private $buildingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowBuildingRequestInterface $showBuildingRequest,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBuildingRequest = $showBuildingRequest;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildingRequest->getColonyId(),
            $userId
        );

        $building = $this->buildingRepository->find($this->showBuildingRequest->getBuildingId());
        if ($building === null) {
            return;
        }

        $alternativeBuildings = $this->buildingFieldAlternativeRepository->getByBuildingId(
            (int) $building->getId()
        );

        if ($alternativeBuildings === []) {
            $useableFieldTypes = $building->getBuildableFields();
        } else {
            $useableFieldTypes = current($alternativeBuildings)->getBuilding()->getBuildableFields();
        }

        $game->setPageTitle($building->getName());
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/buildinginfo');
        $game->setTemplateVar('buildingdata', $building);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('ALTERNATIVE_BUILDINGS', $alternativeBuildings);
        $game->setTemplateVar('USEABLE_FIELD_TYPES', $useableFieldTypes);
    }
}
