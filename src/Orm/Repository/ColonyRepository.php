<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegionSettlement;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

final class ColonyRepository extends EntityRepository implements ColonyRepositoryInterface
{
    public function prototype(): ColonyInterface
    {
        return new Colony();
    }

    public function save(ColonyInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ColonyInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getAmountByUser(UserInterface $user, int $colonyType): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(c.id) from %s c WHERE c.user_id = :userId AND c.colonies_classes_id IN (
                    SELECT cc.id FROM %s cc WHERE cc.type = :type
                )',
                Colony::class,
                ColonyClass::class
            )
        )->setParameters([
            'userId' => $user,
            'type' => $colonyType
        ])->getSingleScalarResult();
    }

    public function getStartingByFaction(int $factionId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT c FROM %s c INDEX BY c.id
                 JOIN %s sm
                 WITH c.starsystem_map_id = sm.id
                 WHERE c.user_id = :userId AND c.colonies_classes_id IN (
                    SELECT pt.id FROM %s pt WHERE pt.allow_start = :allowStart
                ) AND sm.systems_id IN (
                    SELECT m.systems_id FROM %s m WHERE m.systems_id > 0 AND m.admin_region_id IN (
                        SELECT mrs.region_id from %s mrs WHERE mrs.faction_id = :factionId
                    )
                )',
                Colony::class,
                StarSystemMap::class,
                ColonyClass::class,
                Map::class,
                MapRegionSettlement::class
            )
        )->setParameters([
            'allowStart' => 1,
            'userId' => GameEnum::USER_NOONE,
            'factionId' => $factionId
        ])->getResult();
    }

    public function getByPosition(StarSystemMapInterface $sysmap): ?ColonyInterface
    {
        return $this->findOneBy([
            'starsystem_map_id' => $sysmap->getId()
        ]);
    }

    public function getOrderedListByUser(UserInterface $user): iterable
    {
        return $this->findBy(
            ['user_id' => $user],
            ['colonies_classes_id' => 'asc', 'id' => 'asc']
        );
    }

    public function getByTick(int $tick): iterable
    {
        /**
         * @todo the tick value is not in use atm
         */
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT c FROM %s c WHERE c.user_id != :userId',
                Colony::class
            )
        )->setParameters([
            'userId' => GameEnum::USER_NOONE,
        ])->getResult();
    }

    public function getColonized(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT c FROM %s c WHERE c.user_id != :userId',
                Colony::class
            )
        )->setParameters([
            'userId' => GameEnum::USER_NOONE,
        ])->getResult();
    }
}
