<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Colony;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class PlanetColonization implements PlanetColonizationInterface
{
    /**
     * @var PlanetFieldRepositoryInterface
     */
    private $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
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

        $colony->updateColonySurface();
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

        $colony->upperStorage(CommodityTypeEnum::GOOD_BUILDING_MATERIALS, 150);
        $colony->upperStorage(CommodityTypeEnum::GOOD_FOOD, 100);
    }
}