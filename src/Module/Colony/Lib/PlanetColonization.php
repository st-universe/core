<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use RuntimeException;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\ColonyEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
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
    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository, private CommodityRepositoryInterface $commodityRepository, private StorageManagerInterface $storageManager, private ColonyLibFactoryInterface $colonyLibFactory, private ColonyRepositoryInterface $colonyRepository, private UserRepositoryInterface $userRepository, private BuildingManagerInterface $buildingManager) {}

    #[Override]
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

        $commodity = $this->commodityRepository->find(CommodityTypeEnum::COMMODITY_BUILDING_MATERIALS);
        if ($commodity === null) {
            throw new RuntimeException('commodity does not exist');
        }

        $this->storageManager->upperStorage(
            $colony,
            $commodity,
            150
        );
    }
}
