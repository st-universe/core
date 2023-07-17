<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlanetColonization implements PlanetColonizationInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        CommodityRepositoryInterface $commodityRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        BuildingManagerInterface $buildingManager
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->commodityRepository = $commodityRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->buildingManager = $buildingManager;
    }

    public function colonize(
        ColonyInterface $colony,
        int $userId,
        BuildingInterface $building,
        ?PlanetFieldInterface $field = null
    ): void {
        if (!$colony->isFree()) {
            return;
        }

        $this->colonyLibFactory->createColonySurface($colony)->updateSurface();

        if ($field === null) {
            $list = $this->planetFieldRepository->getByColonyAndType(
                $colony->getId(),
                ColonyEnum::COLONY_FIELDTYPE_MEADOW
            );

            shuffle($list);

            /** @var PlanetFieldInterface $field */
            $field = current($list);
        }

        $field->setBuilding($building);

        $colony->setWorkless($building->getHousing());
        $colony->setEps($building->getEpsStorage());

        $this->buildingManager->finish($field, true);

        $colony->setUser($this->userRepository->find($userId));
        $colony->setName(_('Kolonie'));

        $this->colonyRepository->save($colony);
        $this->planetFieldRepository->save($field);

        $this->colonyStorageManager->upperStorage(
            $colony,
            $this->commodityRepository->find(CommodityTypeEnum::COMMODITY_BUILDING_MATERIALS),
            150
        );
    }
}
