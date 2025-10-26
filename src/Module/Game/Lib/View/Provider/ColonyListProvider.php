<?php

declare(strict_types=1);

namespace  Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyListItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyListProvider implements ViewComponentProviderInterface
{
    public function __construct(private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository, private PlanetFieldRepositoryInterface $planetFieldRepository, private ColonyLibFactoryInterface $colonyLibFactory, private ModuleQueueRepositoryInterface $moduleQueueRepository, private BuildingCommodityRepositoryInterface $buildingCommodityRepository)
    {
    }

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colonyList = $game->getUser()->getColonies()->toArray();

        $game->setTemplateVar(
            'COLONY_LIST',
            array_map(
                fn (Colony $colony): ColonyListItemInterface => $this->colonyLibFactory->createColonyListItem($colony),
                $colonyList
            )
        );
        $game->setTemplateVar(
            'PRODUCTION_LIST',
            $this->buildingCommodityRepository->getProductionSumForAllUserColonies($game->getUser())
        );
        $game->setTemplateVar(
            'TERRAFORMING_LIST',
            $this->colonyTerraformingRepository->getByColony($colonyList)
        );
        $game->setTemplateVar(
            'BUILDINGJOB_LIST',
            $this->planetFieldRepository->getInConstructionByUser($userId)
        );
        $game->setTemplateVar(
            'MODULE_LIST',
            $this->moduleQueueRepository->getByUser($userId)
        );
    }
}
