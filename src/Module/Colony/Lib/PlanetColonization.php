<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Colony;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class PlanetColonization implements PlanetColonizationInterface
{
    private $planetFieldRepository;

    private $commodityRepository;

    private $colonyStorageManager;

    private $colonyLibFactory;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        CommodityRepositoryInterface $commodityRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->commodityRepository = $commodityRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function colonize(
        Colony $colony,
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
                COLONY_FIELDTYPE_MEADOW
            );

            shuffle($list);

            $field = current($list);
        }
        $field->setBuilding($building);
        $field->setIntegrity($building->getIntegrity());
        $field->setActive(1);

        $this->planetFieldRepository->save($field);

        $colony->upperMaxBev($building->getHousing());
        $colony->upperMaxEps($building->getEpsStorage());
        $colony->upperMaxStorage($building->getStorage());
        $colony->upperWorkers($building->getWorkers());
        $colony->lowerWorkless($building->getWorkers());
        $colony->upperWorkless($building->getHousing());
        $colony->setUserId($userId);
        $colony->upperEps($building->getEpsStorage());
        $colony->setName(_('Kolonie'));
        $colony->save();

        $this->colonyStorageManager->upperStorage(
            $colony,
            $this->commodityRepository->find(CommodityTypeEnum::GOOD_FOOD),
            100
        );
        $this->colonyStorageManager->upperStorage(
            $colony,
            $this->commodityRepository->find(CommodityTypeEnum::GOOD_BUILDING_MATERIALS),
            150
        );
    }
}