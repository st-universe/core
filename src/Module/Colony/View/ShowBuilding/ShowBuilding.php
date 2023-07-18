<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\PlanetFieldTypeBuildingInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ShowBuilding implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDING';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ShowBuildingRequestInterface $showBuildingRequest;

    private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ShowBuildingRequestInterface $showBuildingRequest,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->showBuildingRequest = $showBuildingRequest;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->buildingRepository = $buildingRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildingRequest->getColonyId(),
            $userId,
            false
        );

        $building = $this->buildingRepository->find($this->showBuildingRequest->getBuildingId());
        if ($building === null) {
            return;
        }

        $alternativeBuildings = $this->buildingFieldAlternativeRepository->getByBuildingIdAndResearchedByUser(
            (int) $building->getId(),
            (int) $userId
        );

        if ($alternativeBuildings === []) {
            $useableFieldTypes = $building->getBuildableFields();
        } else {
            $useableFieldTypes = current($alternativeBuildings)->getBuilding()->getBuildableFields();
        }

        //filter by view
        $useableFieldTypes = array_filter(
            $useableFieldTypes->toArray(),
            fn(PlanetFieldTypeBuildingInterface $pftb): bool => $pftb->getView()
        );

        // @todo: Code verschoenern
        $storage        = $colony->getStorage();
        $buildingcount  = $colony->getEps() / $building->getEpsCost();
        foreach ($building->getCosts() as $cost) {
            if ($storage[$cost->getCommodityId()] != null) {
                $need = $storage[$cost->getCommodityId()]->getAmount() / $cost->getAmount();
                $buildingcount = min($need, $buildingcount);
            } else {
                $buildingcount = 0;
            }
        }
        if ($building->hasLimitColony()) {
            if ($this->planetFieldRepository->getCountByColonyAndBuilding($colony->getId(), $building->getId()) >= $building->getLimitColony()) {
                $buildingcount = 0;
            } else {
                $buildingcount = min($buildingcount, $building->getLimitColony());
            }
        }
        if ($building->hasLimit()) {
            if ($this->planetFieldRepository->getCountByBuildingAndUser($building->getId(), $userId) >= $building->getLimit()) {
                $buildingcount = 0;
            } else {
                $buildingcount = min($buildingcount, $building->getLimit());
            }
        }
        if ($buildingcount < 0) {
            $buildingcount = 0;
        }

        $game->setPageTitle($building->getName());
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/buildinginfo');
        $game->setTemplateVar('buildingdata', $building);
        $game->setTemplateVar('buildingcount', floor($buildingcount));
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('ALTERNATIVE_BUILDINGS', $alternativeBuildings);
        $game->setTemplateVar('USEABLE_FIELD_TYPES', $useableFieldTypes);
        $game->setTemplateVar(
            'ENERGY_PRODUCTION_PREVIEW',
            $this->colonyLibFactory->createEpsProductionPreviewWrapper($colony)
        );
        $game->setTemplateVar(
            'COMMODITY_PRODUCTION_PREVIEW',
            $this->colonyLibFactory->createColonyProductionPreviewWrapper(
                $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
            )
        );
    }
}
