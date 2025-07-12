<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use RuntimeException;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class PlanetColonization implements PlanetColonizationInterface
{
    private const int COLONY_FIELDTYPE_MEADOW = 101;

    public function __construct(
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly CommodityRepositoryInterface $commodityRepository,
        private readonly StorageManagerInterface $storageManager,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly BuildingManagerInterface $buildingManager
    ) {}

    #[Override]
    public function colonize(
        Colony $colony,
        User $user,
        Building $building,
        ?PlanetField $field = null
    ): void {
        if (!$colony->isFree()) {
            return;
        }

        $this->colonyLibFactory->createColonySurface($colony)->updateSurface();

        if ($field === null) {
            $list = $this->planetFieldRepository->getByColonyAndType(
                $colony->getId(),
                self::COLONY_FIELDTYPE_MEADOW
            );

            shuffle($list);

            /** @var PlanetField $field */
            $field = current($list);
        }

        $field->setBuilding($building);

        $colony->getChangeable()->setWorkless($building->getHousing());
        $colony->getChangeable()->setEps($building->getEpsStorage());

        $this->buildingManager->finish($field, true);

        $colony->setUser($user);
        $colony->setName(_('Kolonie'));

        $this->colonyRepository->save($colony);
        $this->planetFieldRepository->save($field);

        $commodity = $this->commodityRepository->find(CommodityTypeConstants::COMMODITY_BUILDING_MATERIALS);
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
