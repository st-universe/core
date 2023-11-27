<?php

declare(strict_types=1);

namespace  Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyListItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ColonyListProvider implements ViewComponentProviderInterface
{
    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ModuleQueueRepositoryInterface $moduleQueueRepository;

    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;


    public function __construct(
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        ModuleQueueRepositoryInterface $moduleQueueRepository,
        BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->moduleQueueRepository = $moduleQueueRepository;
        $this->buildingCommodityRepository = $buildingCommodityRepository;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colonyList = $game->getUser()->getColonies()->toArray();

        $game->setTemplateVar(
            'COLONY_LIST',
            array_map(
                fn (ColonyInterface $colony): ColonyListItemInterface => $this->colonyLibFactory->createColonyListItem($colony),
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
