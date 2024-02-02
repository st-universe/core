<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegionSettlement;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Colony>
 */
final class ColonyRepository extends EntityRepository implements ColonyRepositoryInterface
{
    public function prototype(): ColonyInterface
    {
        return new Colony();
    }

    public function save(ColonyInterface $colony): void
    {
        $em = $this->getEntityManager();

        $em->persist($colony);
    }

    public function delete(ColonyInterface $colony): void
    {
        $em = $this->getEntityManager();

        $em->remove($colony);
        $em->flush();
    }

    public function getAmountByUser(UserInterface $user, int $colonyType): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(c.id) from %s c WHERE c.user_id = :userId AND c.colonies_classes_id IN (
                        SELECT cc.id FROM %s cc WHERE cc.type = :type
                    )',
                    Colony::class,
                    ColonyClass::class
                )
            )
            ->setParameters([
                'userId' => $user,
                'type' => $colonyType
            ])
            ->getSingleScalarResult();
    }

    public function getStartingByFaction(int $factionId): array
    {
        return $this->getEntityManager()
            ->createQuery(
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
            )
            ->setParameters([
                'allowStart' => 1,
                'userId' => UserEnum::USER_NOONE,
                'factionId' => $factionId
            ])
            ->getResult();
    }

    public function getByPosition(StarSystemMapInterface $sysmap): ?ColonyInterface
    {
        return $this->findOneBy([
            'starsystem_map_id' => $sysmap->getId()
        ]);
    }

    public function getForeignColoniesInBroadcastRange(
        StarSystemMapInterface $systemMap,
        UserInterface $user
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                     JOIN %s sm
                     WITH c.starsystem_map_id = sm.id
                     WHERE c.user_id NOT IN (:ignoreIds)
                     AND sm.systems_id = :systemId
                     AND sm.sx BETWEEN (:sx - 1) AND (:sx + 1)
                     AND sm.sy BETWEEN (:sy - 1) AND (:sy + 1)',
                    Colony::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ignoreIds' => [$user->getId(), UserEnum::USER_NOONE],
                'systemId' => $systemMap->getSystem()->getId(),
                'sx' => $systemMap->getSx(),
                'sy' => $systemMap->getSy()
            ])
            ->getResult();
    }

    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    WHERE MOD(c.user_id, :groupCount) + 1 = :groupId
                    AND c.user_id != :userId',
                    Colony::class
                )
            )
            ->setParameters([
                'groupId' => $batchGroup,
                'groupCount' => $batchGroupCount,
                'userId' => UserEnum::USER_NOONE
            ])
            ->getResult();
    }

    public function getColonized(): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c WHERE c.user_id != :userId',
                    Colony::class
                )
            )
            ->setParameters([
                'userId' => UserEnum::USER_NOONE,
            ])
            ->getResult();
    }

    public function getColoniesNetWorth(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('sum', 'sum', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT u.id as user_id, bc.commodity_id AS commodity_id, SUM(bc.count) AS sum
                FROM stu_user u
                JOIN stu_colonies c
                ON u.id = c.user_id 
                JOIN stu_colonies_fielddata cf
                ON cf.colonies_id = c.id
                JOIN stu_buildings_cost bc 
                ON cf.buildings_id = bc.buildings_id 
                WHERE u.id >= :firstUserId
                AND cf.buildings_id IS NOT NULL
                AND cf.aktiv = 1
                GROUP BY u.id, bc.commodity_id',
                $rsm
            )
            ->setParameters(['firstUserId' => UserEnum::USER_FIRST_ID])
            ->getResult();
    }

    public function getColoniesProductionNetWorth(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('sum', 'sum', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT c.user_id, bc.commodity_id, SUM(bc.count) AS sum
                FROM stu_colonies c
                JOIN stu_colonies_fielddata cf
                ON cf.colonies_id = c.id 
                JOIN stu_buildings_commodity bc
                ON cf.buildings_id = bc.buildings_id
                JOIN stu_commodity co
                ON bc.commodity_id = co.id
                WHERE co.type = :typeStandard
                AND co.name != \'Latinum\'
                AND bc.count > 0
                AND cf.aktiv = 1
                GROUP BY c.user_id, bc.commodity_id',
                $rsm
            )
            ->setParameters(['typeStandard' => CommodityTypeEnum::COMMODITY_TYPE_STANDARD])
            ->getResult();
    }

    public function getSatisfiedWorkerTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('satisfied', 'satisfied', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT c.user_id,
                    LEAST(
                        COALESCE(SUM(c.bev_work), 0),
                        (SELECT COALESCE(SUM(bc.count), 0)
                        FROM stu_colonies c2
                        JOIN stu_colonies_fielddata cf
                        ON cf.colonies_id = c2.id
                        JOIN stu_buildings b
                        ON cf.buildings_id = b.id
                        JOIN stu_buildings_commodity bc
                        ON b.id = bc.buildings_id
                        WHERE c2.user_id = c.user_id
                        AND bc.commodity_id = :lifeStandard
                        AND cf.aktiv = 1)
                    ) AS satisfied
                FROM stu_colonies c
                WHERE c.user_id >= :firstUserId
                GROUP BY c.user_id
                ORDER BY satisfied DESC
                LIMIT 10',
                $rsm
            )
            ->setParameters([
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'lifeStandard' => CommodityTypeEnum::COMMODITY_EFFECT_LIFE_STANDARD
            ])
            ->getResult();
    }

    public function getPirateTargets(ShipInterface $ship): array
    {
        $layer = $ship->getLayer();
        if ($layer === null) {
            return [];
        }

        $range = $ship->getSensorRange() * 2;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT c FROM %s c
                JOIN %s sm
                WITH c.starsystem_map_id = sm.id
                JOIN %s s
                WITH sm.systems_id = s.id
                JOIN %s m
                WITH s.id = m.systems_id
                JOIN %s u
                WITH c.user_id = u.id
                WHERE s.cx BETWEEN :minX AND :maxX
                AND s.cy BETWEEN :minY AND :maxY
                AND m.layer_id = :layer
                AND u.id >= :firstUserId
                AND u.state >= :stateActive
                AND u.creation < :fourMonthEarlier
                AND (u.vac_active = false OR u.vac_request_date > :vacationThreshold)',
                Colony::class,
                StarSystemMap::class,
                StarSystem::class,
                Map::class,
                User::class
            )
        )
            ->setParameters([
                'minX' => $ship->getCx() - $range,
                'maxX' => $ship->getCx() + $range,
                'minY' => $ship->getCY() - $range,
                'maxY' => $ship->getCY() + $range,
                'layer' => $layer,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'stateActive' => UserEnum::USER_STATE_ACTIVE,
                'fourMonthEarlier' => time() - TimeConstants::EIGHT_WEEKS_IN_SECONDS,
                'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS
            ])
            ->getResult();
    }
}
