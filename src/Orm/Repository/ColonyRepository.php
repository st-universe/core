<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\NoResultException;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegionSettlement;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;

/**
 * @extends EntityRepository<Colony>
 */
final class ColonyRepository extends EntityRepository implements ColonyRepositoryInterface
{
    #[\Override]
    public function prototype(): Colony
    {
        $colony = new Colony();
        $changeable = new ColonyChangeable($colony);
        $colony->setChangeable($changeable);

        return $colony;
    }

    #[\Override]
    public function save(Colony $colony): void
    {
        $em = $this->getEntityManager();

        $em->persist($colony);
    }

    #[\Override]
    public function delete(Colony $colony): void
    {
        $em = $this->getEntityManager();

        $em->remove($colony);
        $em->flush(); //TODO really neccessary?
    }

    #[\Override]
    public function getAmountByUser(User $user, ColonyTypeEnum $colonyType): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(c.id) from %s c WHERE c.user_id = :userId AND c.colonyClass IN (
                        SELECT cc FROM %s cc WHERE cc.type = :type
                    )',
                    Colony::class,
                    ColonyClass::class
                )
            )
            ->setParameters([
                'userId' => $user,
                'type' => $colonyType->value
            ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getStartingByFaction(int $factionId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c INDEX BY c.id
                     JOIN %s sm
                     WITH c.starsystemMap = sm
                     WHERE c.user_id = :userId AND c.colonyClass IN (
                        SELECT cc FROM %s cc WHERE cc.allow_start = :allowStart
                    ) AND sm.systems_id IN (
                        SELECT m.systems_id FROM %s m WHERE m.systems_id > 0 AND m.admin_region_id IN (
                            SELECT mrs.region_id from %s mrs WHERE mrs.faction_id = :factionId
                        ) AND m.id IN (SELECT l.id FROM %s l WHERE l.layer IN (SELECT ly FROM %s ly WHERE ly.is_colonizable = :true))
                    )',
                    Colony::class,
                    StarSystemMap::class,
                    ColonyClass::class,
                    Map::class,
                    MapRegionSettlement::class,
                    Location::class,
                    Layer::class
                )
            )
            ->setParameters([
                'allowStart' => 1,
                'userId' => UserConstants::USER_NOONE,
                'factionId' => $factionId,
                'true' => true
            ])
            ->getResult();
    }

    #[\Override]
    public function getForeignColoniesInBroadcastRange(
        StarSystemMap $systemMap,
        User $user
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                     JOIN %s sm
                     WITH c.starsystemMap = sm
                     WHERE c.user_id NOT IN (:ignoreIds)
                     AND sm.systems_id = :systemId
                     AND sm.sx BETWEEN (:sx - 1) AND (:sx + 1)
                     AND sm.sy BETWEEN (:sy - 1) AND (:sy + 1)',
                    Colony::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ignoreIds' => [$user->getId(), UserConstants::USER_NOONE],
                'systemId' => $systemMap->getSystem()->getId(),
                'sx' => $systemMap->getSx(),
                'sy' => $systemMap->getSy()
            ])
            ->getResult();
    }

    #[\Override]
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): array
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
                'userId' => UserConstants::USER_NOONE
            ])
            ->getResult();
    }

    #[\Override]
    public function getColonized(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c WHERE c.user_id != :userId',
                    Colony::class
                )
            )
            ->setParameters([
                'userId' => UserConstants::USER_NOONE,
            ])
            ->getResult();
    }

    #[\Override]
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
                JOIN stu_colony c
                ON u.id = c.user_id 
                JOIN stu_colonies_fielddata cf
                ON cf.colony_id = c.id
                JOIN stu_buildings_cost bc 
                ON cf.buildings_id = bc.buildings_id
                WHERE u.id >= :firstUserId
                AND cf.buildings_id IS NOT NULL
                AND cf.aktiv = 1
                GROUP BY u.id, bc.commodity_id',
                $rsm
            )
            ->setParameters(['firstUserId' => UserConstants::USER_FIRST_ID])
            ->getResult();
    }

    #[\Override]
    public function getColoniesProductionNetWorth(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('sum', 'sum', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
            'SELECT c.user_id, bc.commodity_id, SUM(bc.count) AS sum
                FROM stu_colony c
                JOIN stu_colonies_fielddata cf
                ON cf.colony_id = c.id
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
            ->setParameters(['typeStandard' => CommodityTypeConstants::COMMODITY_TYPE_STANDARD])
            ->getResult();
    }

    #[\Override]
    public function getSatisfiedWorkerTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('satisfied', 'satisfied', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
            'SELECT user_id, SUM(satisfied) AS satisfied
                FROM ( SELECT c.user_id,
                            LEAST( COALESCE(cc.bev_work, 0),
                            ( SELECT COALESCE(SUM(bc.count), 0)
                                FROM stu_colonies_fielddata cf
                                JOIN stu_buildings b
                                ON cf.buildings_id = b.id
                                JOIN stu_buildings_commodity bc
                                ON b.id = bc.buildings_id
                                WHERE cf.colony_id = c.id
                                AND bc.commodity_id = :lifeStandard
                                AND cf.aktiv = 1
                            )) AS satisfied
                        FROM stu_colony c
                        JOIN stu_colony_changeable cc
                        ON c.id = cc.colony_id
                        WHERE c.user_id >= :firstUserId) AS colonies
                GROUP BY user_id
                ORDER BY 2 DESC
                LIMIT 10',
                $rsm
            )
            ->setParameters([
                'firstUserId' => UserConstants::USER_FIRST_ID,
                'lifeStandard' => CommodityTypeConstants::COMMODITY_EFFECT_LIFE_STANDARD
            ])
            ->getResult();
    }

    #[\Override]
    public function getPirateTargets(SpacecraftWrapperInterface $wrapper): array
    {
        $layer = $wrapper->get()->getLayer();
        if ($layer === null) {
            return [];
        }

        $location = $wrapper->get()->getLocation();
        $range = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT c FROM %s c
                JOIN %s sm
                WITH c.starsystemMap = sm
                JOIN %s s
                WITH sm.systems_id = s.id
                JOIN %s m
                WITH s.id = m.systems_id
                JOIN %s l
                WITH m.id = l.id
                JOIN %s ly
                WITH l.layer = ly
                JOIN %s u
                WITH c.user = u
                JOIN %s ur
                WITH ur.user = u
                LEFT JOIN %s w
                WITH u = w.user
                WHERE l.cx BETWEEN :minX AND :maxX
                AND l.cy BETWEEN :minY AND :maxY
                AND ly.id = :layerId
                AND u.id >= :firstUserId
                AND u.state >= :stateActive
                AND ur.creation < :fourMonthEarlier
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)
                AND COALESCE(w.protection_timeout, 0) < :currentTime',
                Colony::class,
                StarSystemMap::class,
                StarSystem::class,
                Map::class,
                Location::class,
                Layer::class,
                User::class,
                UserRegistration::class,
                PirateWrath::class
            )
        )
            ->setParameters([
                'minX' => $location->getCx() - $range,
                'maxX' => $location->getCx() + $range,
                'minY' => $location->getCy() - $range,
                'maxY' => $location->getCy() + $range,
                'layerId' => $layer->getId(),
                'firstUserId' => UserConstants::USER_FIRST_ID,
                'stateActive' => UserStateEnum::ACTIVE->value,
                'fourMonthEarlier' => time() - TimeConstants::EIGHT_WEEKS_IN_SECONDS,
                'false' => false,
                'vacationThreshold' => time() - UserConstants::VACATION_DELAY_IN_SECONDS,
                'currentTime' => time()
            ])
            ->getResult();
    }

    #[\Override]
    public function getClosestColonizableColonyDistance(SpacecraftWrapperInterface $wrapper): ?int
    {
        $spacecraft = $wrapper->get();
        $location = $spacecraft->getLocation();

        if ($location instanceof StarSystemMap) {
            return $this->getClosestColonizableColonyInSystem($spacecraft);
        } else {
            return $this->getClosestSystemWithColonizableColonies($spacecraft);
        }
    }

    private function getClosestColonizableColonyInSystem(Spacecraft $spacecraft): ?int
    {
        $systemMap = $spacecraft->getStarsystemMap();
        if ($systemMap === null) {
            return null;
        }

        $currentColony = $systemMap->getColony();
        if (
            $currentColony !== null &&
            $currentColony->getUserId() === UserConstants::USER_NOONE &&
            $currentColony->getColonyClass()->getAllowStart()
        ) {
            return null;
        }

        try {
            $result = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT MIN(ABS(sm.sx - :sx) + ABS(sm.sy - :sy)) as distance
                        FROM %s c
                        JOIN %s sm
                        WITH c.starsystemMap = sm
                        JOIN %s cc
                        WITH c.colonyClass = cc
                        WHERE c.user_id = :nooneUserId
                        AND cc.allow_start = :allowStart
                        AND sm.systems_id = :systemId
                        AND sm.id != :currentMapId',
                    Colony::class,
                    StarSystemMap::class,
                    ColonyClass::class
                )
            )
                ->setParameters([
                    'sx' => $systemMap->getSx(),
                    'sy' => $systemMap->getSy(),
                    'systemId' => $systemMap->getSystemId(),
                    'currentMapId' => $systemMap->getId(),
                    'nooneUserId' => UserConstants::USER_NOONE,
                    'allowStart' => true
                ])
                ->getSingleScalarResult();

            return $result > 0 ? (int)$result : null;
        } catch (NoResultException) {
            return null;
        }
    }


    private function getClosestSystemWithColonizableColonies(Spacecraft $spacecraft): ?int
    {
        $currentLocation = $spacecraft->getLocation();
        $currentX = $currentLocation->getCx();
        $currentY = $currentLocation->getCy();
        $currentLayer = $currentLocation->getLayer();

        if ($currentLayer === null) {
            return null;
        }

        $currentLayerId = $currentLayer->getId();

        try {
            $result = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT MIN(ABS(l.cx - :currentX) + ABS(l.cy - :currentY)) as distance
                        FROM %s c
                        JOIN %s sm
                        WITH c.starsystemMap = sm
                        JOIN %s s
                        WITH sm.systems_id = s.id
                        JOIN %s m
                        WITH s.id = m.systems_id
                        JOIN %s l
                        WITH m.id = l.id
                        JOIN %s ly
                        WITH l.layer = ly
                        JOIN %s cc
                        WITH c.colonyClass = cc
                        WHERE c.user_id = :nooneUserId
                        AND cc.allow_start = :allowStart
                        AND ly.id = :currentLayerId',
                    Colony::class,
                    StarSystemMap::class,
                    StarSystem::class,
                    Map::class,
                    Location::class,
                    Layer::class,
                    ColonyClass::class
                )
            )
                ->setParameters([
                    'currentX' => $currentX,
                    'currentY' => $currentY,
                    'currentLayerId' => $currentLayerId,
                    'nooneUserId' => UserConstants::USER_NOONE,
                    'allowStart' => true
                ])
                ->getSingleScalarResult();

            return $result > 0 ? (int)$result : null;
        } catch (NoResultException) {
            return null;
        }
    }
}
