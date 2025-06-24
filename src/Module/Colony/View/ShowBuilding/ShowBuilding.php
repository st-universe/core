<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuilding;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldTypeBuildingInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ShowBuilding implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BUILDING';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private PlanetFieldRepositoryInterface $planetFieldRepository, private ShowBuildingRequestInterface $showBuildingRequest, private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository, private BuildingRepositoryInterface $buildingRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $building = $this->buildingRepository->find($this->showBuildingRequest->getBuildingId());
        if ($building === null) {
            return;
        }

        $alternativeBuildings = $this->buildingFieldAlternativeRepository->getByBuildingIdAndResearchedByUser(
            $building->getId(),
            $userId
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

        $game->setPageTitle($building->getName());
        $game->setMacroInAjaxWindow('html/colony/component/buildingInfo.twig');
        $game->setTemplateVar('buildingdata', $building);
        $game->setTemplateVar('HOST', $host);
        $game->setTemplateVar('ALTERNATIVE_BUILDINGS', $alternativeBuildings);
        $game->setTemplateVar('USEABLE_FIELD_TYPES', $useableFieldTypes);

        $this->setBuildingLimit($building, $host, $game);
    }

    private function setBuildingLimit(BuildingInterface $building, PlanetFieldHostInterface $host, GameControllerInterface $game): void
    {
        $buildingcount = null;

        if ($host instanceof ColonyInterface) {
            $storage        = $host->getStorage();
            $buildingcount  = $host->getChangeable()->getEps() / $building->getEpsCost();
            foreach ($building->getCosts() as $cost) {
                if ($storage[$cost->getCommodityId()] != null) {
                    $need = $storage[$cost->getCommodityId()]->getAmount() / $cost->getAmount();
                    $buildingcount = min($need, $buildingcount);
                } else {
                    $buildingcount = 0;
                }
            }
        }

        if ($building->hasLimitColony()) {
            if ($this->planetFieldRepository->getCountByHostAndBuilding($host, $building->getId()) >= $building->getLimitColony()) {
                $buildingcount = 0;
            } else {
                $buildingcount = min($buildingcount, $building->getLimitColony());
            }
        }
        if ($building->hasLimit()) {
            if ($this->planetFieldRepository->getCountByBuildingAndUser($building->getId(), $host->getUser()->getId()) >= $building->getLimit()) {
                $buildingcount = 0;
            } else {
                $buildingcount = min($buildingcount, $building->getLimit());
            }
        }
        if ($buildingcount < 0) {
            $buildingcount = 0;
        }

        if ($buildingcount !== null) {
            $game->setTemplateVar('BUILDING_COUNT', floor($buildingcount));
        }
    }
}
