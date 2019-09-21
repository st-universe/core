<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class PlanetColonization implements PlanetColonizationInterface
{
    private $planetFieldRepository;

    private $commodityRepository;

    private $colonyStorageManager;

    private $colonyLibFactory;

    private $colonyRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        CommodityRepositoryInterface $commodityRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->commodityRepository = $commodityRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyRepository = $colonyRepository;
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

        $this->colonyRepository->save($colony);

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