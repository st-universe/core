<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Orm\Entity\BuildingUpgradeCost;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;

return [
    ColonyShipRepairRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyShipRepairRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyShipRepair::class);
    },
    BuildingUpgradeCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingUpgradeCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgradeCost::class);
    },
    DatabaseCategoryRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseCategoryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseCategory::class);
    },
    DatabaseEntryRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseEntryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseEntry::class);
    },
];