<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Orm\Entity\BuildingCost;
use Stu\Orm\Entity\BuildingFieldAlternative;
use Stu\Orm\Entity\BuildingGood;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\BuildingUpgradeCost;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseType;
use Stu\Orm\Entity\DatabaseUser;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\SessionString;
use Stu\Orm\Entity\TerraformingCost;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\UserIpTable;

return [
    ColonyShipRepairRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyShipRepairRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyShipRepair::class);
    },
    BuildingCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingCost::class);
    },
    BuildingFieldAlternativeRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingFieldAlternativeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingFieldAlternative::class);
    },
    BuildingGoodRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingGoodRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingGood::class);
    },
    BuildingUpgradeRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingUpgradeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgrade::class);
    },
    BuildingUpgradeCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingUpgradeCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgradeCost::class);
    },
    BuildplanHangarRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildplanHangarRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildplanHangar::class);
    },
    BuildplanModuleRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildplanModuleRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildplanModule::class);
    },
    CommodityRepositoryInterface::class => function (
        ContainerInterface $c
    ): CommodityRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Commodity::class);
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
    DatabaseTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseType::class);
    },
    DatabaseUserRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseUserRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseUser::class);
    },
    FactionRepositoryInterface::class => function (
        ContainerInterface $c
    ): FactionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Faction::class);
    },
    MapBorderTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): MapBorderTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(MapBorderType::class);
    },
    NoteRepositoryInterface::class => function (
        ContainerInterface $c
    ): NoteRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Note::class);
    },
    PlanetFieldTypeBuildingRepositoryInterface::class => function (
        ContainerInterface $c
    ): PlanetFieldTypeBuildingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldTypeBuilding::class);
    },
    PlanetFieldTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): PlanetFieldTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldType::class);
    },
    ResearchRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Research::class);
    },
    ResearchedRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchedRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Researched::class);
    },
    ResearchDependencyRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchDependencyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ResearchDependency::class);
    },
    SessionStringRepositoryInterface::class => function (
        ContainerInterface $c
    ): SessionStringRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(SessionString::class);
    },
    TerraformingCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): TerraformingCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TerraformingCost::class);
    },
    TorpedoTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): TorpedoTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TorpedoType::class);
    },
    UserIpTableRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserIpTableRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserIpTable::class);
    }
];